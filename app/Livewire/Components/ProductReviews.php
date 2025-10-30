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

    /** Sorting & Filter */
    public string $sortBy = 'newest';     // newest|oldest|highest_rating|lowest_rating
    public string $filterByRating = 'all';// all|1|2|3|4|5

    /** Modal state untuk entangle di Blade */
    public bool $showWriteReviewModal = false;

    /** Form ulasan baru */
    public array $newReview = [
        'rating'  => 5,
        'title'   => '',
        'comment' => '',
    ];

    protected array $rules = [
        'newReview.rating'  => 'required|integer|min:1|max:5',
        'newReview.title'   => 'nullable|string|max:255',
        'newReview.comment' => 'required|string|min:10|max:1000',
    ];

    protected array $messages = [
        'newReview.rating.required' => 'Rating harus dipilih.',
        'newReview.rating.min'      => 'Rating minimal 1 bintang.',
        'newReview.rating.max'      => 'Rating maksimal 5 bintang.',
        'newReview.comment.required'=> 'Komentar harus diisi.',
        'newReview.comment.min'     => 'Komentar minimal 10 karakter.',
        'newReview.comment.max'     => 'Komentar maksimal 1000 karakter.',
    ];

    protected string $paginationTheme = 'tailwind';

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    /** Sync paginate saat sort/filter berubah */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedFilterByRating(): void
    {
        $this->resetPage();
    }

    /** Buka modal (sinkron dengan Blade: entangle + event) */
    public function openWriteReviewModal(): void
    {
        $this->resetErrorBag();
        $this->newReview = ['rating' => 5, 'title' => '', 'comment' => ''];
        $this->showWriteReviewModal = true;

        // kompatibel dengan listener x-on:open-review-modal.window di Blade
        $this->dispatch('open-review-modal');
    }

    /** Tutup modal & reset form */
    public function closeWriteReviewModal(): void
    {
        $this->showWriteReviewModal = false;
        $this->resetValidation();
        $this->newReview = ['rating' => 5, 'title' => '', 'comment' => ''];
    }

    /** Submit ulasan */
    public function submitReview()
    {
        $this->validate();

        // Wajib login sebagai customer
        if (!auth('customer')->check()) {
            session()->flash('error', 'Anda harus login untuk menulis ulasan.');
            return redirect()->route('auth.login');
        }

        // Cegah double review oleh customer yang sama
        $existingReview = ProductReview::where('product_id', $this->product->id)
            ->where('customer_id', auth('customer')->id())
            ->first();

        if ($existingReview) {
            session()->flash('error', 'Anda sudah memberikan ulasan untuk produk ini.');
            return null;
        }

        // Simpan ulasan (default pending moderasi)
        ProductReview::create([
            'product_id'   => $this->product->id,
            'customer_id'  => auth('customer')->id(),
            'rating'       => (int) $this->newReview['rating'],
            'title'        => $this->newReview['title'] ?: null,
            'comment'      => $this->newReview['comment'],
            'is_approved'  => false,
        ]);

        $this->product->refresh();
        $this->resetPage();

        session()->flash('success', 'Ulasan Anda berhasil dikirim dan akan ditampilkan setelah moderasi.');
        $this->closeWriteReviewModal();

        return null;
    }

    /** Reviews untuk tampilan (hanya yang terverifikasi) */
    public function getReviewsProperty()
    {
        $query = $this->product->reviews()
            ->with('customer')
            ->where('is_approved', true);

        if ($this->filterByRating !== 'all') {
            $query->where('rating', (int) $this->filterByRating);
        }

        switch ($this->sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'highest_rating':
                $query->orderBy('rating', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'lowest_rating':
                $query->orderBy('rating', 'asc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(10);
    }

    /** Statistik rating (hanya dari ulasan terverifikasi) */
    public function getReviewStatsProperty(): array
    {
        $reviews = $this->product->reviews()
            ->where('is_approved', true)
            ->get();

        $totalReviews = $reviews->count();
        $avgRating    = $totalReviews ? round($reviews->avg('rating'), 1) : 0.0;
        $avgInt       = (int) floor($avgRating);

        $ratingCounts = $reviews->groupBy('rating')->map->count();
        $ratingDistribution = collect([5,4,3,2,1])->mapWithKeys(function ($star) use ($ratingCounts, $totalReviews) {
            $count = (int) ($ratingCounts[$star] ?? 0);
            $percentage = $totalReviews > 0 ? (int) round(($count * 100) / $totalReviews) : 0;
            return [$star => compact('count', 'percentage')];
        });

        return [
            'total'        => $totalReviews,
            'average'      => $avgRating,
            'average_int'  => max(0, min(5, $avgInt)),
            'distribution' => $ratingDistribution,
        ];
    }

    public function render()
    {
        return view('livewire.components.product-reviews', [
            'reviews'     => $this->reviews,
            'reviewStats' => $this->reviewStats,
        ]);
    }
}
