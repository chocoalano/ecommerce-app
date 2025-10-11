<?php
namespace App\Services;

use App\Repositories\Product\ProductReviewRepository;

class ProductReviewService
{
    protected $repo;
    public function __construct(ProductReviewRepository $repo)
    {
        $this->repo = $repo;
    }
    public function submitReview(array $data)
    {
        // Validasi rating, komentar, dsb bisa ditambah di sini
        return $this->repo->create($data);
    }
    public function approveReview($id)
    {
        return $this->repo->update($id, ['is_approved' => true]);
    }
    public function getProductReviews($productId, $approved = true)
    {
        return $this->repo->byProduct($productId)->where('is_approved', $approved);
    }
    public function getCustomerReviews($customerId)
    {
        return $this->repo->byCustomer($customerId);
    }
    public function deleteReview($id)
    {
        return $this->repo->delete($id);
    }
}
