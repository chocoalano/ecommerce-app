<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address; // pastikan ada, atau sesuaikan namespace/model Address kamu
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
        $cart = Cart::with(['cartItems.variant.product'])
            ->when(Auth::guard('customer')->id(), fn($q) => $q->where('user_id', Auth::guard('customer')->id()))
            ->when(!Auth::guard('customer')->id(), fn($q) => $q->where('session_id', $request->session()->getId()))
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Keranjang kosong.');
        }

        // Recalc total agar sinkron
        $cartService->recalculate($cart);

        return view('pages.checkout.index', compact('cart'));
    }

    public function place(Request $request, CartService $cartService, MidtransGateway $gateway)
    {
        $validated = $request->validate([
            'first_name'      => ['required','string','max:100'],
            'last_name'       => ['required','string','max:100'],
            'phone'           => ['required','string','max:30'],
            'address'         => ['required','string','max:500'],
            'city'            => ['required','string','max:100'],
            'province'        => ['required','string','max:100'],
            'zip'             => ['required','string','max:20'],
            'payment_method'  => ['required', Rule::in(['bank_transfer','credit_card','e_wallet','cod'])],
            // optional:
            'billing_same'    => ['sometimes','boolean'],
        ]);

        $cart = Cart::with(['cartItems.variant.product'])
            ->when(Auth::id(), fn($q) => $q->where('user_id', Auth::id()))
            ->when(!Auth::id(), fn($q) => $q->where('session_id', $request->session()->getId()))
            ->lockForUpdate()
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang kosong.'])->withInput();
        }

        // Pastikan total terbaru
        $cartService->recalculate($cart);

        // 1) Simpan alamat pengiriman (dan billing = sama, untuk sederhana)
        //    >>> SESUAIKAN field Address::create([...]) dengan skema Address kamu <<<
        $shipping = Address::create([
            'user_id'      => Auth::id(),
            'fullname'     => trim($validated['first_name'].' '.$validated['last_name']), // atau first_name/last_name di tabelmu
            'phone'        => $validated['phone'],
            'address'      => $validated['address'],     // mis. line1
            'city'         => $validated['city'],
            'province'     => $validated['province'],
            'postal_code'  => $validated['zip'],
        ]);

        $billingId = $shipping->id; // jika beda alamat, buat Address lagi dari input billing

        // 2) Buat Order + OrderItems (sesuai field model)
        $order = DB::transaction(function () use ($cart, $shipping, $billingId) {
            $orderNo = 'ORD-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));

            /** @var Order $order */
            $order = Order::create([
                'order_no'         => $orderNo,
                'user_id'          => Auth::id(),
                'currency'         => $cart->currency ?? 'IDR',
                'status'           => 'pending',
                'subtotal_amount'  => (float) $cart->subtotal_amount,
                'discount_amount'  => (float) $cart->discount_amount,
                'shipping_amount'  => (float) $cart->shipping_amount,
                'tax_amount'       => (float) $cart->tax_amount,
                'grand_total'      => (float) $cart->grand_total,
                'shipping_address_id' => $shipping->id,
                'billing_address_id'  => $billingId,
                'applied_promos'   => $cart->applied_promos, // array -> cast ke json otomatis
                'notes'            => null, // nanti diupdate dgn info payment
                'placed_at'        => now(),
            ]);

            foreach ($cart->cartItems as $ci) {
                $product   = $ci->variant?->product;
                $variant   = $ci->variant;
                $unitPrice = (float) $ci->unit_price;
                $rowTotal  = (float) $ci->row_total;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product?->id,
                    'variant_id'      => $variant?->id,
                    'name'            => $product?->name ?? $variant?->name ?? ('Variant #'.$ci->variant_id),
                    'sku'             => $variant?->sku ?? ($product?->sku ?? null),
                    'qty'             => (int) $ci->qty,
                    'unit_price'      => $unitPrice,
                    'discount_amount' => 0,
                    'row_total'       => $rowTotal,
                    'meta_json'       => $ci->meta_json,
                ]);
            }

            return $order;
        });

        // 3) Jika COD: langsung kosongkan cart & selesai
        if ($validated['payment_method'] === 'cod') {
            $cart->cartItems()->delete();
            $cart->update([
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount'      => 0,
                'grand_total'     => 0,
            ]);

            $order->update(['status' => 'confirmed', 'notes' => json_encode(['payment' => 'COD'])]);
            return redirect()->route('checkout.thankyou', $order);
        }

        // 4) Non-COD → buat transaksi ke gateway (Midtrans Snap sebagai contoh)
        try {
            $payment = $gateway->createPayment($order, $validated['payment_method']);

            // simpan token/url di kolom notes (json) — model Order tidak punya kolom payment_*
            $notes = [
                'gateway' => 'midtrans',
                'token'   => $payment['token']        ?? null,
                'url'     => $payment['redirect_url'] ?? null,
                'method'  => $validated['payment_method'],
            ];
            $order->update(['notes' => json_encode($notes)]);

            if (!empty($payment['redirect_url'])) {
                // kosongkan cart sebelum diarahkan (opsional, atau kosongkan setelah callback sukses)
                $cart->cartItems()->delete();
                $cart->update([
                    'subtotal_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_amount' => 0,
                    'tax_amount'      => 0,
                    'grand_total'     => 0,
                ]);

                return redirect()->away($payment['redirect_url']);
            }

            return redirect()->route('checkout.thankyou', $order);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['payment' => 'Gagal memproses pembayaran. Coba lagi atau ganti metode.']);
        }
    }

    public function thankyou(Order $order)
    {
        return view('pages.checkout.thankyou', compact('order'));
    }
}
