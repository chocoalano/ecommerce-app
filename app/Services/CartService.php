<?php

namespace App\Services;

use App\Models\CartProduct\Cart;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function addItem(Cart $cart, int $productId, int $qty, array $meta = []): void
    {
        $product = \App\Models\Product\Product::findOrFail($productId);
        $cart->addOrIncrementProduct($product, $qty, null, $meta);
    }

    public function addItems(Cart $cart, array $items): void
    {
        DB::transaction(function () use ($cart, $items) {
            foreach ($items as $it) {
                $this->addItem($cart, (int)$it['product_id'], (int)$it['qty'], (array)($it['meta_json'] ?? []));
            }
        });
    }

    public function recalculate(Cart $cart): void
    {
        $cart->recalcTotals();
    }
}
