# Repository & Service Pattern Implementation

## Deskripsi

Implementasi Repository Pattern dan Service Pattern untuk aplikasi Laravel e-commerce. Pattern ini menyediakan abstraksi data layer dan business logic layer untuk maintainability dan testability yang lebih baik.

## Struktur Arsitektur

```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── ProductRepositoryInterface.php
│   │   └── CategoryRepositoryInterface.php
│   └── Services/
│       └── ProductServiceInterface.php
├── Repositories/
│   ├── BaseRepository.php
│   └── Product/
│       ├── ProductRepository.php
│       └── CategoryRepository.php
├── Services/
│   └── Product/
│       └── ProductService.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Keunggulan Pattern

### ✅ Repository Pattern Benefits
- **Data Abstraction**: Memisahkan logika database dari business logic
- **Testability**: Mudah di-mock untuk unit testing
- **Maintainability**: Centralized database queries
- **Flexibility**: Mudah switch database atau ORM
- **Consistency**: Standardized data access methods

### ✅ Service Pattern Benefits
- **Business Logic**: Centralized business rules dan logic
- **Reusability**: Service dapat digunakan di multiple controllers
- **Transaction Management**: Handle complex operations dengan transactions
- **Caching**: Centralized caching strategy
- **Validation**: Business-level validation

## Installation & Setup

### 1. Register Service Provider

Tambahkan ke `config/app.php`:

```php
'providers' => [
    // ... other providers
    App\Providers\RepositoryServiceProvider::class,
],
```

### 2. Publish Service Provider (Optional)

```bash
php artisan make:provider RepositoryServiceProvider
```

## Penggunaan

### 1. Basic Repository Usage

```php
// Inject repository di controller
public function __construct(ProductRepositoryInterface $productRepository)
{
    $this->productRepository = $productRepository;
}

// Basic operations
$product = $this->productRepository->find(1);
$products = $this->productRepository->all();
$activeProducts = $this->productRepository->getActive();
$productBySlug = $this->productRepository->findBySlug('product-slug');
```

### 2. Advanced Repository Methods

```php
// Chaining methods
$products = $this->productRepository
    ->with(['categories', 'media'])
    ->where('is_active', true)
    ->orderBy('created_at', 'desc')
    ->paginate(15);

// Search with filters
$products = $this->productRepository->search('query', [
    'categories' => [1, 2, 3],
    'min_price' => 100,
    'max_price' => 1000,
    'brand' => ['Nike', 'Adidas'],
    'in_stock' => true
]);

// Complex queries
$relatedProducts = $this->productRepository->getRelated($product, 8);
$featuredProducts = $this->productRepository->getFeatured(12);
```

### 3. Service Layer Usage

```php
// Inject service di controller
public function __construct(ProductServiceInterface $productService)
{
    $this->productService = $productService;
}

// High-level operations
$product = $this->productService->getProductBySlug('product-slug');
$pricing = $this->productService->calculatePrice($product, 2);
$available = $this->productService->checkAvailability($productId, 5);

// Complex business operations
$newProduct = $this->productService->createProduct($data);
$updatedProduct = $this->productService->updateProduct($id, $data);
$success = $this->productService->updateStock($productId, 100, 'Restock');
```

## Controller Implementation

### Best Practice Controller

```php
<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProductServiceInterface;
use App\Traits\HasBreadcrumb;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HasBreadcrumb;

    protected ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function show(string $slug)
    {
        // Service handles all business logic
        $product = $this->productService->getProductBySlug($slug);
        $relatedProducts = $this->productService->getRelatedProducts($product);
        $pricing = $this->productService->calculatePrice($product);
        
        // Controller only handles presentation logic
        $breadcrumbItems = $this->buildProductBreadcrumb($product);
        
        return view('pages.products.detail', compact(
            'product', 'relatedProducts', 'pricing', 'breadcrumbItems'
        ));
    }
}
```

## Fitur Repository

### Base Repository Methods

```php
// CRUD Operations
$repository->all();                          // Get all records
$repository->find($id);                     // Find by ID
$repository->findOrFail($id);              // Find or throw exception
$repository->create($data);                // Create new record
$repository->update($id, $data);           // Update record
$repository->delete($id);                  // Delete record

// Query Building
$repository->with(['relation']);           // Eager loading
$repository->where('field', 'value');      // Where condition
$repository->orderBy('field', 'desc');     // Order by
$repository->limit(10);                    // Limit results
$repository->paginate(15);                 // Paginate

// Advanced Searches
$repository->findBy('field', 'value');     // Find by specific field
$repository->findWhere(['field' => 'value']); // Find with conditions
$repository->updateOrCreate($criteria, $data); // Update or create
```

### Product Repository Specific Methods

```php
// Product-specific methods
$repository->findBySlug($slug);            // Find by slug
$repository->findBySku($sku);              // Find by SKU
$repository->getActive();                  // Active products only
$repository->getByCategory($categoryId);   // Products by category
$repository->search($query, $filters);    // Advanced search
$repository->getFeatured();               // Featured products
$repository->getLatest();                 // Latest products
$repository->getLowStock($threshold);     // Low stock products
$repository->getRelated($product);        // Related products
$repository->updateStock($id, $quantity); // Update stock
```

## Fitur Service

### Product Service Methods

```php
// High-level business operations
$service->getAllProducts($filters);        // Get filtered products
$service->getProductBySlug($slug);        // Get product with details
$service->searchProducts($query, $filters); // Search with caching
$service->getFeaturedProducts();          // Cached featured products
$service->getRelatedProducts($product);   // Smart recommendations

// Business logic operations
$service->createProduct($data);           // Create with relationships
$service->updateProduct($id, $data);     // Update with validation
$service->deleteProduct($id);            // Delete with cleanup
$service->updateStock($id, $qty, $reason); // Stock with logging
$service->checkAvailability($id, $qty);  // Availability check
$service->calculatePrice($product, $qty); // Price with promotions

// Analytics & Statistics
$service->getProductStatistics($id);     // Product analytics
$service->getRecommendations($criteria); // Smart recommendations

// Bulk operations
$service->bulkUpdateStatus($ids, $status); // Bulk status update
$service->bulkUpdatePrices($updates);    // Bulk price update
$service->bulkUpdateStock($updates);     // Bulk stock update
```

## Caching Strategy

Service layer mengimplementasikan caching strategy:

```php
// Auto-caching di service layer
$products = $productService->getFeaturedProducts(); // Cached 30 minutes
$product = $productService->getProductBySlug($slug); // Cached 1 hour
$related = $productService->getRelatedProducts($product); // Cached 30 minutes

// Cache invalidation otomatis
$productService->updateProduct($id, $data); // Auto clear related cache
$productService->updateStock($id, $qty);    // Auto clear product cache
```

## Testing

### Repository Testing

```php
// Mock repository untuk testing
$mockRepository = Mockery::mock(ProductRepositoryInterface::class);
$mockRepository->shouldReceive('find')
               ->with(1)
               ->andReturn($expectedProduct);

$this->app->instance(ProductRepositoryInterface::class, $mockRepository);
```

### Service Testing

```php
// Test service dengan mocked repository
public function testGetProductBySlug()
{
    $mockRepo = Mockery::mock(ProductRepositoryInterface::class);
    $service = new ProductService($mockRepo);
    
    $mockRepo->shouldReceive('findBySlug')
             ->with('test-slug')
             ->andReturn($expectedProduct);
    
    $result = $service->getProductBySlug('test-slug');
    $this->assertEquals($expectedProduct, $result);
}
```

## Error Handling

### Repository Error Handling

```php
try {
    $product = $this->productRepository->findOrFail($id);
} catch (ModelNotFoundException $e) {
    throw new ProductNotFoundException("Product with ID {$id} not found");
}
```

### Service Error Handling

```php
public function updateProduct(int $id, array $data): Product
{
    DB::beginTransaction();
    try {
        $product = $this->productRepository->update($id, $data);
        
        // Handle relationships
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }
        
        $this->clearCache($product->slug);
        
        DB::commit();
        return $product;
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update product', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
```

## Performance Optimization

### Eager Loading

```php
// Repository dengan eager loading
public function getProductWithDetails(string $slug): ?Product
{
    return $this->with([
        'categories',
        'media',
        'reviews.user',
        'promotions'
    ])->findBySlug($slug);
}
```

### Query Optimization

```php
// Optimized search dengan indexing
public function search(string $query, array $filters = []): LengthAwarePaginator
{
    return $this->query
        ->select(['id', 'name', 'slug', 'base_price', 'stock']) // Select specific columns
        ->where('is_active', true)
        ->where('name', 'LIKE', "%{$query}%")
        ->with(['primaryMedia:id,product_id,url']) // Limit relationship columns
        ->paginate(15);
}
```

## Monitoring & Logging

### Service Logging

```php
// Auto-logging di service operations
public function updateStock(int $productId, int $quantity, string $reason = ''): bool
{
    $oldStock = $this->productRepository->find($productId)->stock;
    $result = $this->productRepository->updateStock($productId, $quantity);
    
    if ($result) {
        Log::info('Stock updated', [
            'product_id' => $productId,
            'old_stock' => $oldStock,
            'new_stock' => $quantity,
            'reason' => $reason,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
    }
    
    return $result;
}
```

## Best Practices

### 1. Dependency Injection
- Selalu inject interface, bukan concrete class
- Gunakan constructor injection untuk dependencies

### 2. Single Responsibility
- Repository hanya handle data access
- Service handle business logic
- Controller handle HTTP requests/responses

### 3. Error Handling
- Repository throw database exceptions
- Service handle business exceptions
- Controller handle HTTP exceptions

### 4. Caching
- Implement caching di service layer
- Clear cache saat data berubah
- Gunakan cache tags untuk bulk clearing

### 5. Testing
- Mock dependencies di unit tests
- Test business logic di service tests
- Test HTTP behavior di controller tests

## Migration Guide

### Dari Direct Model Usage

```php
// Before (direct model usage)
public function show($slug)
{
    $product = Product::with(['categories', 'media'])
                     ->where('slug', $slug)
                     ->firstOrFail();
    
    return view('product.show', compact('product'));
}

// After (with repository & service)
public function show($slug)
{
    $product = $this->productService->getProductBySlug($slug);
    $pricing = $this->productService->calculatePrice($product);
    
    return view('product.show', compact('product', 'pricing'));
}
```

### Benefits Setelah Migration

1. **Testability**: Mudah mock dan test
2. **Maintainability**: Logic terpusat
3. **Reusability**: Service dapat digunakan di multiple places
4. **Performance**: Built-in caching dan optimization
5. **Consistency**: Standardized data access patterns

## Troubleshooting

### Common Issues

1. **Binding Not Found**
   ```
   Solution: Register binding di RepositoryServiceProvider
   ```

2. **Method Not Found**
   ```
   Solution: Pastikan method ada di interface dan implementation
   ```

3. **Cache Not Clearing**
   ```
   Solution: Implementasikan cache invalidation di service methods
   ```

4. **N+1 Queries**
   ```
   Solution: Gunakan eager loading di repository methods
   ```

## Extending the Pattern

### Adding New Repository

1. Create interface di `app/Contracts/Repositories/`
2. Create implementation di `app/Repositories/`
3. Register binding di `RepositoryServiceProvider`
4. Create service interface dan implementation
5. Register service binding

### Custom Repository Methods

```php
// Add to interface
public function getCustomMethod(array $criteria): Collection;

// Implement in repository
public function getCustomMethod(array $criteria): Collection
{
    return $this->query
        ->where($criteria)
        ->with(['relations'])
        ->get();
}
```

Repository dan Service Pattern ini memberikan foundation yang solid untuk aplikasi Laravel e-commerce yang scalable dan maintainable.
