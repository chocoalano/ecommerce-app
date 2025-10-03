<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddManyToCartRequest;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        // Ambil cart berdasar user login atau session id aktif
        $cart = Cart::with(['cartItems.variant.product'])->firstWhere([
            'user_id' => auth()->guard('customer')->id(),
        ]) ?? Cart::with(['cartItems.variant.product'])->firstWhere([
            'session_id' => session()->getId(),
        ]);
        return view('pages.auth.cart', compact('cart'));
    }

    /**
     * Add single item
     */
    public function store(AddToCartRequest $request, CartService $svc)
    {
        // Pastikan session_id terisi bila user belum login
        $userId    = Auth::guard('customer')->id();
        $sessionId = session()->getId();

        $cart = Cart::firstOrCreate(
            [
                'user_id'    => $userId,
                'session_id' => $userId ? null : $sessionId, // bila login, kosongkan session_id agar unik
            ],
            [
                'currency' => $request->input('currency', 'IDR'),
            ]
        );

        if ($cart->currency !== strtoupper($request->input('currency', $cart->currency))) {
            $cart->currency = strtoupper($request->input('currency', $cart->currency));
            $cart->save();
        }

        $svc->addItem(
            $cart,
            (int) $request->variant_id,
            (int) $request->qty,
            (array) $request->input('meta_json', [])
        );

        // Response untuk AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item ditambahkan ke keranjang.',
                'cart_id' => $cart->id,
                'totals'  => [
                    'subtotal' => $cart->subtotal_amount,
                    'discount' => $cart->discount_amount,
                    'shipping' => $cart->shipping_amount,
                    'tax'      => $cart->tax_amount,
                    'grand'    => $cart->grand_total,
                ],
            ]);
        }

        // Response untuk form biasa (non-AJAX)
        return redirect()->route('cart.index')->with('success', 'Item ditambahkan ke keranjang.');
    }

    /**
     * Add many items (batch)
     */
    public function storeMany(AddManyToCartRequest $request, CartService $svc)
    {
        $userId    = $request->input('user_id') ?? Auth::id();
        $sessionId = $request->input('session_id') ?? session()->getId();

        $cart = Cart::firstOrCreate(
            [
                'user_id'    => $userId,
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
                'totals'  => [
                    'subtotal' => $cart->subtotal_amount,
                    'discount' => $cart->discount_amount,
                    'shipping' => $cart->shipping_amount,
                    'tax'      => $cart->tax_amount,
                    'grand'    => $cart->grand_total,
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

        $item->qty       = (int) $request->qty;
        $item->row_total = $item->unit_price * $item->qty;
        $item->save();

        $svc->recalculate($item->cart);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Qty diperbarui.', 'totals' => [
                'subtotal' => $item->cart->subtotal_amount,
                'discount' => $item->cart->discount_amount,
                'shipping' => $item->cart->shipping_amount,
                'tax'      => $item->cart->tax_amount,
                'grand'    => $item->cart->grand_total,
            ]]);
        }

        return back()->with('success', 'Qty diperbarui.');
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
            return response()->json(['message' => 'Item dihapus.', 'totals' => [
                'subtotal' => $cart->subtotal_amount,
                'discount' => $cart->discount_amount,
                'shipping' => $cart->shipping_amount,
                'tax'      => $cart->tax_amount,
                'grand'    => $cart->grand_total,
            ]]);
        }

        return back()->with('success', 'Item dihapus.');
    }
}
