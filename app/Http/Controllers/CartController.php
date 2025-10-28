<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddManyToCartRequest;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Models\CartProduct\Cart;
use App\Models\CartProduct\CartItem;
use App\Services\CartService;
use App\Services\RajaOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Ambil cart berdasar user login atau session id aktif
        $cart = Cart::with(['items.product'])->firstWhere([
            'customer_id' => auth()->guard('customer')->id(),
        ]) ?? Cart::with(['items.product'])->firstWhere([
            'session_id' => session()->getId(),
        ]);

    // $rajaOngkir = new RajaOngkir();
    // $provinceId = $request->input('province_id', null);
    // $prov = $rajaOngkir->provinces() ?? [];
    // $city = $rajaOngkir->city($provinceId) ?? [];
    $prov = [];
    $city = [];
    if ($request->ajax()) {
        return response()->json([
            'provinces' => $prov,
            'cities' => $city,
        ]);
    }
    // dd($city);
    return view('pages.auth.cart', compact('cart', 'prov', 'city'));
    }

    /**
     * Add single item
     */
    public function store(AddToCartRequest $request, CartService $svc)
    {
        // === 401 jika belum login ===
        if (! Auth::guard('customer')->check()) {
            // Untuk fetch/AJAX atau permintaan JSON → balas JSON 401
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Non-AJAX: abort 401 (bisa diganti redirect()->route('auth.login'))
            abort(Response::HTTP_UNAUTHORIZED);
        }

        try {
            $user = Auth::guard('customer')->user();

            // Normalisasi currency
            $currency = strtoupper($request->input('currency', 'IDR'));

            // Cart hanya untuk user terautentikasi (guest tidak dibuat)
            $cart = Cart::firstOrCreate(
                ['customer_id' => $user->id],
                ['currency' => $currency]
            );

            // Sinkronkan currency jika berubah
            if ($cart->currency !== $currency) {
                $cart->update(['currency' => $currency]);
            }

            // Tambahkan item
            $svc->addItem(
                $cart,
                (int) $request->input('product_id'),
                max(1, (int) $request->input('quantity', 1)),
                (array) $request->input('meta_json', [])
            );

            // Muat relasi seperlunya (hemat kolom)
            $cart->load([
                'items.product' => fn ($q) => $q->select('id', 'name', 'sku', 'base_price'),
            ]);

            // Hitung total qty item
            $itemCount = (int) $cart->items()->sum('qty');

            // Bentuk daftar item untuk preview/response
            $items = $cart->items->map(function ($item) {
                $product = $item->product;
                $unit = (float) $item->unit_price; // atau $product->price jika kebijakanmu begitu
                $qty  = (int) $item->qty;

                return [
                    'id'         => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'name'       => (string) ($product->name ?? 'Item'),
                    'variant'    => (string) ($product->sku ?? null),
                    'qty'        => $qty,
                    'unit_price' => $unit,
                    'image'      => $product->primaryMedia ?? null,
                    'subtotal'   => $qty * $unit,
                ];
            })->values();

            // Totals
            $totals = [
                'subtotal' => (float) $cart->subtotal_amount,
                'discount' => (float) $cart->discount_amount,
                'shipping' => (float) $cart->shipping_amount,
                'tax'      => (float) $cart->tax_amount,
                'grand'    => (float) $cart->grand_total,
            ];

            // === JSON (AJAX/fetch) ===
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Produk berhasil ditambahkan ke keranjang!',
                    // kunci yang kompatibel dengan normalizer front-end
                    'cart_id'    => (int) $cart->id,
                    'cart_count' => $itemCount,
                    'count'      => $itemCount,
                    'items'      => $items,   // front-end kamu menerima "items"
                    'totals'     => $totals,  // front-end kamu menerima "totals"
                ], Response::HTTP_OK);
            }

            // === Non JSON (fallback) ===
            return redirect()
                ->route('cart.index')
                ->with('success', 'Item ditambahkan ke keranjang.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan produk ke keranjang.',
                    'error'   => config('app.debug') ? $e->getMessage() : null,
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return back()->with('error', 'Gagal menambahkan item ke keranjang.');
        }
    }

    /**
     * Add many items (batch)
     */
    public function storeMany(AddManyToCartRequest $request, CartService $svc)
    {
        $userId = $request->input('user_id') ?? Auth::guard('customer')->id();
        $sessionId = $request->input('session_id') ?? session()->getId();

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
            ],
            [
                'currency' => $request->input('currency', 'IDR'),
            ]
        );

        if ($cart->currency !== strtoupper($request->input('currency', $cart->currency))) {
            $cart->currency = strtoupper($request->input('currency', $cart->currency));
            $cart->save();
        }

        $svc->addItems($cart, $request->items);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Items ditambahkan.',
                'cart_id' => $cart->id,
                'totals' => [
                    'subtotal' => $cart->subtotal_amount,
                    'discount' => $cart->discount_amount,
                    'shipping' => $cart->shipping_amount,
                    'tax' => $cart->tax_amount,
                    'grand' => $cart->grand_total,
                ],
            ]);
        }

        // return redirect()->route('cart.index')->with('success', 'Items ditambahkan.');
    }

    /**
     * (Opsional) Update qty item
     */
    public function update(Request $request, CartItem $item, CartService $svc)
    {
        $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $item->update([
            'qty' => (int) $request->qty,
            'row_total' => (int) $request->qty * $item->unit_price,
        ]);
        $item->cart->recalcTotals();

        $item->cart->load('items'); // Ensure items are loaded for count

        return response()->json([
            'success' => true,
            'message' => 'Qty diperbarui.',
            'totals' => [
                'subtotal' => (float) $item->cart->subtotal_amount,
                'discount' => (float) $item->cart->discount_amount,
                'shipping' => (float) $item->cart->shipping_amount,
                'tax' => (float) $item->cart->tax_amount,
                'grand' => (float) $item->cart->grand_total,
                'item_count' => $item->cart->items->count(),
            ],
        ]);
    }

    /**
     * (Opsional) Hapus item
     */
    public function destroy(Request $request, CartItem $item, CartService $svc)
    {
        $cart = $item->cart;
        $item->delete();

        $svc->recalculate($cart);

        if ($request->wantsJson()) {
            $cart->load('items'); // Ensure items are loaded for count

            return response()->json([
                'success' => true,
                'message' => 'Item dihapus.',
                'totals' => [
                    'subtotal' => (float) $cart->subtotal_amount,
                    'discount' => (float) $cart->discount_amount,
                    'shipping' => (float) $cart->shipping_amount,
                    'tax' => (float) $cart->tax_amount,
                    'grand' => (float) $cart->grand_total,
                    'item_count' => $cart->items->count(),
                ],
            ]);
        }

        return back()->with('success', 'Item dihapus.');
    }
}
