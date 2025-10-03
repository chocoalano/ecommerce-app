<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function addItem(Cart $cart, int $variantId, int $qty, array $meta = []): void
    {
        $variant   = ProductVariant::findOrFail($variantId);
        $unitPrice = (float) ($variant->base_price ?? 0); // ganti sesuai field harga Anda

        // Tambah/merge item
        $item = $cart->cartItems()->where('variant_id', $variantId)->first();

        if ($item) {
            $newQty = $item->qty + $qty;
            $item->qty        = $newQty;
            $item->unit_price = $unitPrice; // bisa dikunci saat pertama kali ditambahkan bila perlu
            $item->row_total  = $unitPrice * $newQty;
            $item->meta_json  = array_merge($item->meta_json ?? [], $meta);
            $item->save();
        } else {
            $cart->cartItems()->create([
                'variant_id' => $variantId,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
                'row_total'  => $unitPrice * $qty,
                'meta_json'  => $meta,
            ]);
        }

        $this->recalculate($cart);
    }

    public function addItems(Cart $cart, array $items): void
    {
        DB::transaction(function () use ($cart, $items) {
            foreach ($items as $it) {
                $this->addItem($cart, (int)$it['variant_id'], (int)$it['qty'], (array)($it['meta_json'] ?? []));
            }
        });
    }

    public function recalculate(Cart $cart): void
    {
        $subtotal = (float) $cart->cartItems()->sum('row_total');

        // Atur logic diskon/pajak/ongkir di sini
        $discount = 0.0;
        $shipping = 0.0;
        $tax      = 0.0;

        // contoh simple PPN 11% (opsional)
        // $tax = round(($subtotal - $discount) * 0.11, 2);

        $grand = max(0, ($subtotal - $discount) + $shipping + $tax);

        $cart->fill([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'shipping_amount' => $shipping,
            'tax_amount'      => $tax,
            'grand_total'     => $grand,
        ])->save();
    }
}
