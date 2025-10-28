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
        try {
            $userId = Auth::guard('customer')->id();
            $sessionId = session()->getId();

            // Get or create cart for user or guest
            $cart = Cart::firstOrCreate(
                [
                    'customer_id' => $userId ?? null,
                    'session_id' => $userId ? null : $sessionId,
                ],
                [
                    'currency' => strtoupper($request->input('currency', 'IDR')),
                ]
            );

            // Update currency if different
            $currency = strtoupper($request->input('currency', $cart->currency));
            if ($cart->currency !== $currency) {
                $cart->update(['currency' => $currency]);
            }



            // Add item to cart
            $svc->addItem(
                $cart,
                (int) $request->product_id,
                (int) $request->quantity,
                (array) $request->input('meta_json', [])
            );

            // Prepare response data
            $cart->load(['items.product']);
            $itemCount = $cart->items()->sum('qty');

            $items = $cart->items()->get()->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->product?->name ?? 'Item',
                'variant' => $item->product?->sku,
                'qty' => (int) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'image' => $item->product?->primary_image_url,
            ]);

            $totals = [
                'subtotal' => (float) $cart->subtotal_amount,
                'discount' => (float) $cart->discount_amount,
                'shipping' => (float) $cart->shipping_amount,
                'tax' => (float) $cart->tax_amount,
                'grand' => (float) $cart->grand_total,
            ];
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang!',
                    'cart_count' => $itemCount,
                    'cart_items' => $items,
                    'cart_totals' => $totals,
                    'data' => [
                        'cart_id' => $cart->id,
                        'item_count' => $itemCount,
                        'totals' => $totals,
                    ],
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Item ditambahkan ke keranjang.');

        } catch (\Exception $e) {
            // Jangan dd($e), langsung handle error
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan produk ke keranjang.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan sistem.',
                ], $e->getCode() ?: 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan item ke keranjang.');
        }
    }

    /**
     * Add many items (batch)
     */
    public function storeMany(AddManyToCartRequest $request, CartService $svc)
    {
        $userId = $request->input('user_id') ?? Auth::id();
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

        return redirect()->route('cart.index')->with('success', 'Items ditambahkan.');
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
