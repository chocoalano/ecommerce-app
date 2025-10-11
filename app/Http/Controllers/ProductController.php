<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProductServiceInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Models\CartProduct\Cart;
use App\Models\Product\Product;
use App\Traits\HasBreadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    use HasBreadcrumb;

    protected ProductServiceInterface $productService;
    protected CategoryServiceInterface $categoryService;

    public function __construct(
        ProductServiceInterface $productService,
        CategoryServiceInterface $categoryService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }
    /**
     * Store a newly filtered resource.
     */
    public function index(Request $request)
    {
        // Build filters from request
        $filters = $this->buildFilters($request);

        // Get products using service
        $products = $this->productService->getAllProducts($filters, $filters['per_page']);

        // Get category options for filter
        $categoryOptions = $this->categoryService->getAllCategories();

        // Build breadcrumb
        $breadcrumbItems = $this->buildSimpleBreadcrumb('Produk');

        return view('pages.products.filtered', compact(
            'products',
            'categoryOptions',
            'breadcrumbItems'
        ) + $this->getFilterParams($request));
    }

    /**
     * Build filters from request
     */
    protected function buildFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->get('q', '')),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'min_rating' => $request->get('min_rating'),
            'categories' => (array) $request->get('category', []),
            'in_stock' => (bool) $request->get('in_stock'),
            'on_sale' => (bool) $request->get('on_sale'),
            'features' => (array) $request->get('features', []),
            'sort_by' => $this->mapSortField($request->get('sort', 'popular')),
            'sort_direction' => $this->getSortDirection($request->get('sort', 'popular')),
            'per_page' => max(1, min((int) $request->get('per_page', 24), 60)),
        ];
    }

    /**
     * Map sort parameter to database field
     */
    protected function mapSortField(string $sort): string
    {
        return match ($sort) {
            'new' => 'created_at',
            'price_asc', 'price_desc' => 'base_price',
            'name_asc', 'name_desc' => 'name',
            'rating_asc', 'rating_desc' => 'avg_rating',
            default => 'created_at',
        };
    }

    /**
     * Get sort direction
     */
    protected function getSortDirection(string $sort): string
    {
        return match ($sort) {
            'price_asc', 'name_asc', 'rating_asc' => 'asc',
            'price_desc', 'name_desc', 'rating_desc' => 'desc',
            'new' => 'desc',
            default => 'desc',
        };
    }

    /**
     * Get filter parameters for view
     */
    protected function getFilterParams(Request $request): array
    {
        return [
            'q' => $request->get('q', ''),
            'minPrice' => $request->get('min_price'),
            'maxPrice' => $request->get('max_price'),
            'minRating' => $request->get('min_rating'),
            'inStock' => (bool) $request->get('in_stock'),
            'onSale' => (bool) $request->get('on_sale'),
            'features' => (array) $request->get('features', []),
            'categories' => (array) $request->get('category', []),
            'sort' => $request->get('sort', 'popular'),
            'perPage' => $request->get('per_page', 24),
        ];
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            // Check availability
            $available = $this->productService->checkAvailability(
                $validated['product_id'],
                $validated['quantity']
            );

            if (!$available) {
                return back()->with('error', 'Product is not available or insufficient stock.');
            }

            // Create cart item
            Cart::create($validated);

            return redirect()->route('cart.index')->with('success', 'Product added to cart successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add product to cart. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $sku)
    {
        // Get product using service
        $product = $this->productService->getProductBySku($sku);

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Get related products
        $relatedProducts = $this->productService->getRelatedProducts($product, 8);

        // Calculate pricing
        $pricing = $this->productService->calculatePrice($product, 1);

        // Prepare view data
        $viewData = $this->prepareProductDetailData($product, $pricing);

        // Build breadcrumb
        $primaryCategory = $product->categories->first();
        $breadcrumbItems = $this->buildProductBreadcrumb($product, $primaryCategory);

        return view('pages.products.detail', array_merge($viewData, [
            'relatedProducts' => $relatedProducts,
            'breadcrumbItems' => $breadcrumbItems,
        ]));
    }

    /**
     * Prepare product detail data for view
     */
    protected function prepareProductDetailData(Product $product, array $pricing): array
    {
        return [
            'product' => $product,
            'imageUrl' => $this->getProductImageUrl($product),
            'gallery' => $this->getProductGallery($product),
            'primaryCategory' => $product->categories->first(),
            'ratingDistribution' => $this->getRatingDistribution($product),
            'reviews' => $product->reviews,
            'priceFormatted' => $this->formatPrice($pricing['base_price']),
            'pricing' => $pricing,
        ];
    }

    private function getProductImageUrl(Product $product): string
    {
        $imageUrl = $product->media->first()?->url;

        if ($imageUrl && !$this->isExternalUrl($imageUrl)) {
            return asset($imageUrl);
        }

        return $imageUrl ?: asset('images/smartphone.png');
    }

    private function getProductGallery(Product $product)
    {
        return $product->media
            ->pluck('url')
            ->filter()
            ->map(fn($url) => $this->isExternalUrl($url) ? $url : asset($url));
    }

    /**
     * Check if URL is external
     */
    private function isExternalUrl(string $url): bool
    {
        return str_starts_with($url, 'http://') ||
               str_starts_with($url, 'https://') ||
               str_starts_with($url, 'data:image');
    }

    /**
     * Get product search suggestions (API endpoint)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $filters = ['search' => $query];
        $products = $this->productService->searchProducts($query, $filters, 5);

        $suggestions = collect($products->items())->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $this->getProductImageUrl($product),
                'price' => $this->formatPrice($product->base_price),
            ];
        });

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Check product availability (API endpoint)
     */
    public function checkAvailability(Request $request, int $id)
    {
        $quantity = $request->get('quantity', 1);
        $available = $this->productService->checkAvailability($id, $quantity);

        return response()->json([
            'available' => $available,
            'product_id' => $id,
            'quantity' => $quantity
        ]);
    }

    /**
     * Get product pricing (API endpoint)
     */
    public function pricing(Request $request, string $slug)
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $quantity = $request->get('quantity', 1);
        $pricing = $this->productService->calculatePrice($product, $quantity);

        return response()->json($pricing);
    }

    /**
     * Get featured products (API endpoint)
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', 12);
        $products = $this->productService->getFeaturedProducts($limit);

        return response()->json([
            'products' => $products->map(function ($product) {
                return $this->transformProductForApi($product);
            })
        ]);
    }

    /**
     * Get latest products (API endpoint)
     */
    public function latest(Request $request)
    {
        $limit = $request->get('limit', 12);
        $products = $this->productService->getLatestProducts($limit);

        return response()->json([
            'products' => $products->map(function ($product) {
                return $this->transformProductForApi($product);
            })
        ]);
    }

    /**
     * Transform product for API response
     */
    protected function transformProductForApi(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_desc,
            'brand' => $product->brand,
            'base_price' => $product->base_price,
            'formatted_price' => $this->formatPrice($product->base_price),
            'currency' => $product->currency,
            'stock' => $product->stock,
            'is_active' => $product->is_active,
            'primary_image' => $this->getProductImageUrl($product),
            'categories' => $product->categories->pluck('name'),
            'avg_rating' => $product->avg_rating ?? 0,
            'reviews_count' => $product->reviews_count ?? 0,
            'created_at' => $product->created_at,
        ];
    }

    private function getRatingDistribution(Product $product): array
    {
        $ratingCounts = $product->reviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $totalReviews = $product->reviews_count;

        return collect([5, 4, 3, 2, 1])->mapWithKeys(function ($star) use ($ratingCounts, $totalReviews) {
            $count = $ratingCounts[$star] ?? 0;
            $percentage = $totalReviews > 0 ? round($count * 100 / $totalReviews) : 0;

            return [$star => $percentage];
        })->toArray();
    }

    private function formatPrice(?float $price): ?string
    {
        return $price > 0 ? 'Rp ' . number_format($price, 0, ',', '.') : null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $this->buildFilters($request);

        $products = $this->productService->searchProducts($query, $filters, $filters['per_page']);
        $categoryOptions = $this->categoryService->getAllCategories();
        $breadcrumbItems = $this->buildSearchBreadcrumb($query);

        if ($request->ajax()) {
            return response()->json([
                'products' => collect($products->items())->map(function ($product) {
                    return $this->transformProductForApi($product);
                }),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);
        }

        return view('pages.products.search', compact(
            'products',
            'query',
            'categoryOptions',
            'breadcrumbItems'
        ) + $this->getFilterParams($request));
    }

    /**
     * Get products by category
     */
    public function category(Request $request, string $categorySlug)
    {
        $category = $this->categoryService->getCategoryBySlug($categorySlug);

        if (!$category) {
            abort(404, 'Category not found');
        }

        $filters = $this->buildFilters($request);
        $filters['categories'] = [$category->id];

        $products = $this->productService->getProductsByCategory($category->id, $filters, $filters['per_page']);
        $categoryOptions = $this->categoryService->getAllCategories();
        $breadcrumbItems = $this->buildCategoryBreadcrumb($category);

        return view('pages.products.category', compact(
            'products',
            'category',
            'categoryOptions',
            'breadcrumbItems'
        ) + $this->getFilterParams($request));
    }
}
