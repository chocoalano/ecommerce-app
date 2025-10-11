<?php
namespace App\Repositories\Product;

use App\Models\Product\Wishlist;

class WishlistRepository
{
    public function all($customerId = null)
    {
        return $customerId
            ? Wishlist::where('customer_id', $customerId)->get()
            : Wishlist::all();
    }
    public function find($id)
    {
        return Wishlist::find($id);
    }
    public function create(array $data)
    {
        return Wishlist::create($data);
    }
    public function update($id, array $data)
    {
        $wishlist = Wishlist::findOrFail($id);
        $wishlist->update($data);
        return $wishlist;
    }
    public function delete($id)
    {
        return Wishlist::destroy($id);
    }
    public function items($wishlistId)
    {
        return Wishlist::findOrFail($wishlistId)->items;
    }
}
