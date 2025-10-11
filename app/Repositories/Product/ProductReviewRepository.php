<?php
namespace App\Repositories\Product;

use App\Models\Product\ProductReview;

class ProductReviewRepository
{
    public function all($approved = true)
    {
        return ProductReview::where('is_approved', $approved)->get();
    }
    public function find($id)
    {
        return ProductReview::find($id);
    }
    public function create(array $data)
    {
        return ProductReview::create($data);
    }
    public function update($id, array $data)
    {
        $review = ProductReview::findOrFail($id);
        $review->update($data);
        return $review;
    }
    public function delete($id)
    {
        return ProductReview::destroy($id);
    }
    public function byProduct($productId)
    {
        return ProductReview::where('product_id', $productId)->get();
    }
    public function byCustomer($customerId)
    {
        return ProductReview::where('customer_id', $customerId)->get();
    }
}
