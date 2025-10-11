# Product Controller Implementation with Repository Pattern

## Overview

ProductController telah direfactor untuk menggunakan Repository dan Service Pattern, memberikan separation of concerns yang lebih baik dan code yang lebih maintainable.

## 🔄 Changes Summary

### Before (Direct Model Usage)
```php
// Direct database queries di controller
$products = Product::query()
    ->with(['media:id,product_id,url'])
    ->withAvg('reviews as avg_rating', 'rating')
    ->where('products.is_active', true)
    ->paginate($perPage);
```

### After (Service Pattern)
```php
// Business logic di service layer
$products = $this->productService->getAllProducts($filters, $perPage);
```

## 🏗️ Architecture

```
Controller Layer (ProductController)
    ↓
Service Layer (ProductService)
    ↓
Repository Layer (ProductRepository)
    ↓
Model Layer (Product)
```

## 📝 Implementation Details

### Constructor Dependency Injection

```php
public function __construct(
    ProductServiceInterface $productService,
    CategoryServiceInterface $categoryService
) {
    $this->productService = $productService;
    $this->categoryService = $categoryService;
}
```

### Methods Overview

#### **1. index() - Product Listing**
- **Before**: Complex query building di controller
- **After**: Service-based filtering dengan clean interface

```php
public function index(Request $request)
{
    $filters = $this->buildFilters($request);
    $products = $this->productService->getAllProducts($filters, $filters['per_page']);
    $categoryOptions = $this->categoryService->getAllCategories();

    return view('pages.products.filtered', compact('products', 'categoryOptions'));
}
```

**Features:**
- Filter building helper methods
- Service-based data retrieval
- Breadcrumb integration
- Cached category options

#### **2. show() - Product Detail**
- **Before**: Direct model query dengan manual relationship loading
- **After**: Service-based dengan business logic

```php
public function show(string $slug)
{
    $product = $this->productService->getProductBySlug($slug);
    $relatedProducts = $this->productService->getRelatedProducts($product, 8);
    $pricing = $this->productService->calculatePrice($product, 1);

    return view('pages.products.detail', $viewData);
}
```

**Features:**
- Automatic 404 handling
- Related products recommendation
- Price calculation dengan promotions
- Breadcrumb generation
- View data preparation

#### **3. store() - Add to Cart**
- **Before**: Simple cart creation tanpa validation
- **After**: Availability check dan error handling

```php
public function store(Request $request)
{
    $available = $this->productService->checkAvailability($productId, $quantity);
    if (!$available) {
        return back()->with('error', 'Product not available');
    }
    // Create cart item...
}
```

**Features:**
- Stock availability validation
- Proper error handling
- Success/error flash messages
- Redirect management

#### **4. search() - Product Search**
```php
public function search(Request $request)
{
    $products = $this->productService->searchProducts($query, $filters, $perPage);

    if ($request->ajax()) {
        return response()->json(['products' => $transformedProducts]);
    }

    return view('pages.products.search', compact('products'));
}
```

**Features:**
- AJAX support untuk dynamic loading
- Filter integration
- JSON response untuk API calls
- Breadcrumb untuk search results

#### **5. category() - Category Products**
```php
public function category(Request $request, string $categorySlug)
{
    $category = $this->categoryService->getCategoryBySlug($categorySlug);
    $products = $this->productService->getProductsByCategory($category->id, $filters);

    return view('pages.products.category', compact('products', 'category'));
}
```

## 🔧 Helper Methods

### Filter Building
```php
protected function buildFilters(Request $request): array
{
    return [
        'search' => trim($request->get('q', '')),
        'min_price' => $request->get('min_price'),
        'max_price' => $request->get('max_price'),
        'categories' => (array) $request->get('category', []),
        'in_stock' => (bool) $request->get('in_stock'),
        'sort_by' => $this->mapSortField($request->get('sort', 'popular')),
        'sort_direction' => $this->getSortDirection($request->get('sort')),
        'per_page' => max(1, min((int) $request->get('per_page', 24), 60)),
    ];
}
```

### Sort Mapping
```php
protected function mapSortField(string $sort): string
{
    return match ($sort) {
        'new' => 'created_at',
        'price_asc', 'price_desc' => 'base_price',
        'name_asc', 'name_desc' => 'name',
        default => 'created_at',
    };
}
```

### Data Transformation
```php
protected function transformProductForApi(Product $product): array
{
    return [
        'id' => $product->id,
        'name' => $product->name,
        'slug' => $product->slug,
        'formatted_price' => $this->formatPrice($product->base_price),
        'primary_image' => $this->getProductImageUrl($product),
        'stock' => $product->stock,
        // ... other fields
    ];
}
```

## 🌐 API Endpoints

### Product Suggestions
```php
GET /api/products/suggestions?q=laptop
```

### Availability Check
```php
GET /api/products/{id}/availability?quantity=2
```

### Pricing Information
```php
GET /api/products/{slug}/pricing?quantity=1
```

### Featured Products
```php
GET /api/products/featured?limit=12
```

### Latest Products
```php
GET /api/products/latest?limit=8
```

## 📋 Routes Configuration

```php
// routes/products.php

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/cart/add', [ProductController::class, 'store'])->name('cart.add');
Route::get('/category/{categorySlug}', [ProductController::class, 'category'])->name('products.category');

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/products/suggestions', [ProductController::class, 'suggestions']);
    Route::get('/products/{id}/availability', [ProductController::class, 'checkAvailability']);
    Route::get('/products/{slug}/pricing', [ProductController::class, 'pricing']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/latest', [ProductController::class, 'latest']);
});
```

## 🎯 Benefits Achieved

### 1. **Separation of Concerns**
- Controller: HTTP request/response handling
- Service: Business logic dan rules
- Repository: Data access patterns

### 2. **Improved Testability**
```php
// Easy to mock services untuk testing
$mockProductService = Mockery::mock(ProductServiceInterface::class);
$controller = new ProductController($mockProductService, $mockCategoryService);
```

### 3. **Caching Integration**
- Service layer handles caching automatically
- No cache logic di controller
- Smart cache invalidation

### 4. **Error Handling**
```php
try {
    $available = $this->productService->checkAvailability($productId, $quantity);
} catch (\Exception $e) {
    return back()->with('error', 'Failed to check availability');
}
```

### 5. **Code Reusability**
- Service methods dapat digunakan di multiple controllers
- API dan web routes menggunakan logic yang sama
- Consistent data transformation

### 6. **Performance Optimization**
- Built-in caching di service layer
- Eager loading di repository
- Optimized queries

## 🔄 Migration Steps

### 1. Register Service Provider
```php
// config/app.php
'providers' => [
    App\Providers\RepositoryServiceProvider::class,
],
```

### 2. Update Route Binding
```php
// Update existing routes untuk menggunakan slug instead of SKU
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
```

### 3. Update Views
Views mungkin perlu disesuaikan untuk data structure yang baru:

```blade
{{-- Before --}}
@foreach($products as $product)
    {{ $product->base_price }}
@endforeach

{{-- After (dengan pricing calculation) --}}
@foreach($products as $product)
    {{ $pricing['formatted_price'] ?? $product->base_price }}
@endforeach
```

## 🧪 Testing

### Controller Tests
```php
public function test_index_returns_products()
{
    $this->mockProductService
        ->shouldReceive('getAllProducts')
        ->andReturn($paginatedProducts);

    $response = $this->get('/products');

    $response->assertOk()
            ->assertViewIs('pages.products.filtered')
            ->assertViewHas('products');
}
```

### API Tests
```php
public function test_suggestions_returns_json()
{
    $response = $this->get('/api/products/suggestions?q=laptop');

    $response->assertOk()
            ->assertJsonStructure(['suggestions' => [['id', 'name', 'slug']]]);
}
```

## 📈 Performance Considerations

1. **Caching Strategy**: Service layer auto-caching
2. **Eager Loading**: Relationships loaded efficiently
3. **Query Optimization**: Repository menggunakan selective loading
4. **Pagination**: Built-in pagination support
5. **API Responses**: Transformed data untuk minimal payload

## 🔮 Future Enhancements

1. **Redis Caching**: Untuk high-traffic applications
2. **Search Indexing**: Elasticsearch integration
3. **Rate Limiting**: API endpoint protection
4. **Metrics**: Performance monitoring
5. **A/B Testing**: Product recommendation testing

Repository pattern implementation di ProductController memberikan foundation yang solid untuk scalable e-commerce application! 🚀
