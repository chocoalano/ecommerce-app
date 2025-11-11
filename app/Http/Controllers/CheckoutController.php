<?php

namespace App\Http\Controllers;

use App\Models\Auth\CustomerAddress;
use App\Models\CartProduct\Cart;
use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\OrderItem;
use App\Services\CartService;
use App\Services\MidtransGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        $cart = Cart::with(['items.product'])
            ->when(Auth::guard('customer')->id(), fn($q) => $q->where('customer_id', Auth::guard('customer')->id()))
            ->first();
        if (!$cart || $cart->items->isEmpty()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong.',
                    'redirect_url' => route('cart.index')
                ], 400);
            }
            return redirect()->route('cart.index')->with('warning', 'Keranjang kosong.');
        }

        // Recalc total agar sinkron
        $cartService->recalculate($cart);

        return view('pages.checkout.index', compact('cart'));
    }

    public function place(Request $request, CartService $cartService, MidtransGateway $gateway)
    {
        // Custom validation error handling for AJAX requests
        $validator = \Validator::make($request->all(), [
            'first_name'      => ['required','string','max:100'],
            'last_name'       => ['required','string','max:100'],
            'phone'           => ['required','string','max:30'],
            'address'         => ['required','string','max:500'],
            'city'            => ['required','string','max:100'],
            'province'        => ['required','string','max:100'],
            'zip'             => ['required','string','max:20'],
            'payment_method'  => ['required', Rule::in(['bank_transfer','credit_card','e_wallet','cod','midtrans'])],
            // optional:
            'billing_same'    => ['sometimes','boolean'],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $cart = Cart::with(['items.product'])
            ->when(Auth::guard('customer')->id(), fn($q) => $q->where('customer_id', Auth::guard('customer')->id()))
            ->when(!Auth::guard('customer')->id(), fn($q) => $q->where('session_id', $request->session()->getId()))
            ->lockForUpdate()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong.',
                    'errors' => ['cart' => ['Keranjang kosong.']]
                ], 422);
            }
            return back()->withErrors(['cart' => 'Keranjang kosong.'])->withInput();
        }

        // Pastikan total terbaru
        $cartService->recalculate($cart);

        // 1) Simpan alamat pengiriman (dan billing = sama, untuk sederhana)
        $shipping = CustomerAddress::create([
            'customer_id'      => Auth::guard('customer')->id(),
            'first_name'       => $validated['first_name'],
            'last_name'        => $validated['last_name'],
            'phone'            => $validated['phone'],
            'address'          => $validated['address'],
            'city'             => $validated['city'],
            'province'         => $validated['province'],
            'postal_code'      => $validated['zip'],
        ]);

        $billingId = $shipping->id; // jika beda alamat, buat Address lagi dari input billing

        // 2) Buat Order + OrderItems (sesuai field model)
        $order = DB::transaction(function () use ($cart, $shipping, $billingId, $validated) {
            $orderNo = 'ORD-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));

            /** @var Order $order */
            $order = Order::create([
                'order_no'         => $orderNo,
                'customer_id'      => Auth::guard('customer')->id(),
                'currency'         => $cart->currency ?? 'IDR',
                'status'           => Order::ST_PENDING, // Start with pending, will be updated after payment
                'subtotal_amount'  => (float) $cart->subtotal_amount,
                'discount_amount'  => (float) $cart->discount_amount,
                'shipping_amount'  => (float) $cart->shipping_amount,
                'tax_amount'       => (float) $cart->tax_amount,
                'grand_total'      => (float) $cart->grand_total,
                'shipping_address_id' => $shipping->id,
                'billing_address_id'  => $billingId,
                'applied_promos'   => $cart->applied_promos,
                'notes'            => null,
                'placed_at'        => now(),
            ]);

            foreach ($cart->items as $ci) {
                $product   = $ci->product;
                $unitPrice = (float) $ci->unit_price;
                $rowTotal  = (float) $ci->row_total;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product?->id,
                    'name'            => $product?->name,
                    'sku'             => $product?->sku,
                    'qty'             => (int) $ci->qty,
                    'unit_price'      => $unitPrice,
                    'discount_amount' => 0,
                    'row_total'       => $rowTotal,
                    'meta_json'       => $ci->meta_json,
                ]);
            }

            return $order;
        });

        // 3) Jika COD: langsung konfirmasi dan kosongkan cart
        if ($validated['payment_method'] === 'cod') {
            $cart->items()->delete();
            $cart->update([
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount'      => 0,
                'grand_total'     => 0,
            ]);

            $order->update(['status' => 'confirmed', 'notes' => json_encode(['payment' => 'COD'])]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat dengan metode COD.',
                    'redirect_url' => route('checkout.thankyou', $order),
                    'order' => [
                        'id' => $order->id,
                        'order_no' => $order->order_no,
                        'status' => $order->status,
                        'grand_total' => $order->grand_total
                    ]
                ]);
            }

            return redirect()->route('checkout.thankyou', $order);
        }

        // 4) Payment menggunakan Midtrans (untuk semua metode non-COD)
        try {
            // Gunakan payment method yang dipilih user
            $payment = $gateway->createPayment($order, $validated['payment_method']);

            // simpan token/url di kolom notes (json)
            $notes = [
                'gateway' => 'midtrans',
                'snap_token' => $payment['token'] ?? null,
                'redirect_url' => $payment['redirect_url'] ?? null,
                'payment_method' => $validated['payment_method'],
                'created_at' => now()->toISOString(),
                'initial_status' => Order::ST_PENDING,
            ];
            $order->update([
                'notes' => json_encode($notes),
                'status' => Order::ST_PENDING // Explicitly set pending while waiting for payment
            ]);

            // Clear cart sebelum redirect/response
            $cart->items()->delete();
            $cart->update([
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount'      => 0,
                'grand_total'     => 0,
            ]);

            if (!empty($payment['redirect_url'])) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pembayaran berhasil dibuat. Anda akan diarahkan ke halaman pembayaran Midtrans.',
                        'redirect_url' => $payment['redirect_url'],
                        'external_redirect' => true,
                        'order' => [
                            'id' => $order->id,
                            'order_no' => $order->order_no,
                            'status' => $order->status,
                            'grand_total' => $order->grand_total
                        ],
                        'payment' => [
                            'gateway' => 'midtrans',
                            'method' => 'midtrans',
                            'snap_token' => $payment['token'] ?? null,
                            'redirect_url' => $payment['redirect_url']
                        ]
                    ]);
                }

                return redirect()->away($payment['redirect_url']);
            }

            // Jika tidak ada redirect URL (fallback)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.',
                    'redirect_url' => route('checkout.thankyou', $order),
                    'order' => [
                        'id' => $order->id,
                        'order_no' => $order->order_no,
                        'status' => $order->status,
                        'grand_total' => $order->grand_total
                    ],
                    'payment' => [
                        'gateway' => 'midtrans',
                        'method' => 'midtrans',
                        'snap_token' => $payment['token'] ?? null
                    ]
                ]);
            }

            return redirect()->route('checkout.thankyou', $order);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            \Log::error('Midtrans Payment Error', [
                'order_id' => $order->id ?? null,
                'order_no' => $order->order_no ?? null,
                'payment_method' => 'midtrans',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Thank you page after successful order
     */
    public function thankyou(Order $order)
    {
        // Load relationships untuk display
        $order->load(['items.product', 'shippingAddress', 'customer']);

        // Auto-check payment status jika order masih pending dan menggunakan Midtrans
        if ($order->status === Order::ST_PENDING) {
            $notes = json_decode($order->notes, true) ?? [];

            if (isset($notes['gateway']) && $notes['gateway'] === 'midtrans') {
                \Log::info('Thank You Page - Auto checking payment status', [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                ]);

                try {
                    $midtrans = new MidtransGateway();
                    $status = $midtrans->getTransactionStatus($order->order_no);

                    \Log::info('Midtrans Payment Status Check from Thank You Page', [
                        'order_no' => $order->order_no,
                        'transaction_status' => $status['transaction_status'] ?? 'unknown',
                        'status_code' => $status['status_code'] ?? 'unknown',
                    ]);

                    // Jika payment berhasil (settlement atau capture), update status
                    if (in_array($status['transaction_status'], ['settlement', 'capture'])) {
                        $notes['midtrans_notifications'][] = [
                            'timestamp' => now()->toISOString(),
                            'status' => 'success',
                            'transaction_status' => $status['transaction_status'],
                            'payment_type' => $status['payment_type'] ?? 'unknown',
                            'fraud_status' => $status['fraud_status'] ?? 'accept',
                            'checked_from' => 'thankyou_page',
                        ];

                        $order->update([
                            'status' => Order::ST_PAID,
                            'notes' => json_encode($notes),
                            'paid_at' => now(),
                        ]);

                        \Log::info('Order Status Updated to PAID from Thank You Page', [
                            'order_id' => $order->id,
                            'order_no' => $order->order_no,
                            'previous_status' => Order::ST_PENDING,
                            'new_status' => Order::ST_PAID,
                        ]);

                        // Refresh order untuk menampilkan status terbaru
                        $order->refresh();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error checking payment status from Thank You page', [
                        'order_no' => $order->order_no,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }        return view('pages.checkout.thankyou', compact('order'));
    }

    /**
     * Show order payment status (for checking payment progress)
     */
    public function paymentStatus(Request $request, Order $order)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $notes = json_decode($order->notes, true) ?? [];

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'status' => $order->status,
                    'grand_total' => $order->grand_total,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ],
                'payment' => [
                    'gateway' => $notes['gateway'] ?? null,
                    'method' => $notes['payment_method'] ?? $order->payment_method,
                    'snap_token' => $notes['snap_token'] ?? null,
                    'redirect_url' => $notes['redirect_url'] ?? null
                ]
            ]);
        }

        return redirect()->route('checkout.thankyou', $order);
    }
}
