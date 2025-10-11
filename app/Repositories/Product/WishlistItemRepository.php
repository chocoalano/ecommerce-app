<?php
namespace App\Repositories\Product;

use App\Models\Product\WishlistItem;

class WishlistItemRepository
{
    public function all($wishlistId = null)
    {
        return $wishlistId
            ? WishlistItem::where('wishlist_id', $wishlistId)->get()
            : WishlistItem::all();
    }
    public function find($id)
    {
        return WishlistItem::find($id);
    }
    public function create(array $data)
    {
        return WishlistItem::create($data);
    }
    public function update($id, array $data)
    {
        $item = WishlistItem::findOrFail($id);
        $item->update($data);
        return $item;
    }
    public function delete($id)
    {
        return WishlistItem::destroy($id);
    }
    public function byProduct($productId)
    {
        return WishlistItem::where('product_id', $productId)->get();
    }
}
