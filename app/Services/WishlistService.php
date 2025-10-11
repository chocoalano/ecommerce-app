<?php
namespace App\Services;

use App\Repositories\Product\WishlistRepository;
use App\Repositories\Product\WishlistItemRepository;

class WishlistService
{
    protected $wishlistRepo;
    protected $itemRepo;
    public function __construct(WishlistRepository $wishlistRepo, WishlistItemRepository $itemRepo)
    {
        $this->wishlistRepo = $wishlistRepo;
        $this->itemRepo = $itemRepo;
    }
    public function addProductToWishlist($wishlistId, $productId, array $meta = [])
    {
        return $this->itemRepo->create([
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
            'meta_json' => $meta,
        ]);
    }
    public function removeProductFromWishlist($wishlistId, $productId)
    {
        $item = $this->itemRepo->all($wishlistId)->where('product_id', $productId)->first();
        return $item ? $this->itemRepo->delete($item->id) : false;
    }
    public function getWishlistItems($wishlistId)
    {
        return $this->itemRepo->all($wishlistId);
    }
    public function createWishlist($customerId, $name = 'Default')
    {
        return $this->wishlistRepo->create([
            'customer_id' => $customerId,
            'name' => $name,
        ]);
    }
    public function deleteWishlist($wishlistId)
    {
        return $this->wishlistRepo->delete($wishlistId);
    }
}
