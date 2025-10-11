<?php

namespace App\Livewire\Components;

use App\Models\Product\Product;
use App\Models\Product\ProductReview;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviews extends Component
{
    use WithPagination;

    public Product $product;
    public $sortBy = 'newest';
    public $filterByRating = 'all';
    public $showWriteReviewModal = false;

    // Form fields for new review
    public $newReview = [
        'rating' => 5,
        'title' => '',
        'comment' => '',
    ];

    protected $rules = [
        'newReview.rating' => 'required|integer|min:1|max:5',
        'newReview.title' => 'nullable|string|max:255',
        'newReview.comment' => 'required|string|min:10|max:1000',
    ];

    protected $messages = [
        'newReview.rating.required' => 'Rating harus dipilih.',
        'newReview.rating.min' => 'Rating minimal 1 bintang.',
        'newReview.rating.max' => 'Rating maksimal 5 bintang.',
        'newReview.comment.required' => 'Komentar harus diisi.',
        'newReview.comment.min' => 'Komentar minimal 10 karakter.',
        'newReview.comment.max' => 'Komentar maksimal 1000 karakter.',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatedFilterByRating()
    {
        $this->resetPage();
    }

    public function openWriteReviewModal()
    {
        $this->showWriteReviewModal = true;
        $this->dispatch('open-review-modal');
    }

    public function closeWriteReviewModal()
    {
        $this->showWriteReviewModal = false;
        $this->reset('newReview');
        $this->resetValidation();
    }

    public function submitReview()
    {
        $this->validate();

        // Check if user is authenticated
        if (!auth()->guard('customer')->check()) {
            session()->flash('error', 'Anda harus login untuk menulis ulasan.');
            return redirect()->route('login');
        }

        // Check if user has already reviewed this product
        $existingReview = ProductReview::where('product_id', $this->product->id)
            ->where('customer_id', auth()->guard('customer')->id())
            ->first();

        if ($existingReview) {
            session()->flash('error', 'Anda sudah memberikan ulasan untuk produk ini.');
            return;
        }

        // Create new review
        ProductReview::create([
            'product_id' => $this->product->id,
            'customer_id' => auth()->guard('customer')->id(),
            'rating' => $this->newReview['rating'],
            'title' => $this->newReview['title'],
            'comment' => $this->newReview['comment'],
            'is_approved' => false, // Admin can approve later
        ]);

        // Refresh product reviews
        $this->product->refresh();

        session()->flash('success', 'Ulasan Anda berhasil dikirim dan akan ditampilkan setelah moderasi.');
        $this->closeWriteReviewModal();
    }

    public function getReviewsProperty()
    {
        $query = $this->product->reviews()->with('customer');

        // Apply rating filter
        if ($this->filterByRating !== 'all') {
            $query->where('rating', $this->filterByRating);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'highest_rating':
                $query->orderBy('rating', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'lowest_rating':
                $query->orderBy('rating', 'asc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(10);
    }

    public function getReviewStatsProperty()
    {
        $reviews = $this->product->reviews;
        $totalReviews = $reviews->count();
        $avgRating = $reviews->avg('rating') ?? 0;

        $ratingCounts = $reviews->groupBy('rating')->map->count();
        $ratingDistribution = collect([5, 4, 3, 2, 1])->mapWithKeys(function ($star) use ($ratingCounts, $totalReviews) {
            $count = $ratingCounts[$star] ?? 0;
            $percentage = $totalReviews > 0 ? round($count * 100 / $totalReviews) : 0;
            return [$star => compact('count', 'percentage')];
        });

        return [
            'total' => $totalReviews,
            'average' => $avgRating,
            'average_int' => (int) floor($avgRating),
            'distribution' => $ratingDistribution,
        ];
    }

    public function render()
    {
        return view('livewire.components.product-reviews', [
            'reviews' => $this->reviews,
            'reviewStats' => $this->reviewStats,
        ]);
    }
}
