# Repository & Service Pattern - Quick Reference

## 🏗️ Struktur Yang Dibuat

### Interfaces (Contracts)
- `BaseRepositoryInterface` - Interface dasar untuk semua repository
- `ProductRepositoryInterface` - Interface khusus Product
- `CategoryRepositoryInterface` - Interface khusus Category  
- `ProductServiceInterface` - Interface untuk Product service
- `CategoryServiceInterface` - Interface untuk Category service

### Implementations
- `BaseRepository` - Abstract base class untuk repository
- `ProductRepository` - Implementasi Product repository
- `CategoryRepository` - Implementasi Category repository
- `ProductService` - Business logic untuk Product
- `CategoryService` - Business logic untuk Category

### Service Provider
- `RepositoryServiceProvider` - Binding interfaces ke implementations

### Tests
- `ProductServiceTest` - Unit tests untuk ProductService
- `ProductRepositoryTest` - Feature tests untuk ProductRepository

## 🚀 Quick Start

### 1. Register Service Provider
```php
// config/app.php
'providers' => [
    App\Providers\RepositoryServiceProvider::class,
],
```

### 2. Use in Controller
```php
<?php

class ProductController extends Controller
{
    public function __construct(
        private ProductServiceInterface $productService
    ) {}

    public function show(string $slug)
    {
        $product = $this->productService->getProductBySlug($slug);
        return view('product.show', compact('product'));
    }
}
```

## 📝 Common Usage Patterns

### Repository Methods
```php
// Basic CRUD
$repository->find($id);
$repository->create($data);
$repository->update($id, $data);
$repository->delete($id);

// Querying
$repository->findBySlug($slug);
$repository->getActive();
$repository->search($query, $filters);

// Chaining
$repository->with(['relations'])
          ->where('field', 'value')
          ->orderBy('field')
          ->paginate(15);
```

### Service Methods
```php
// High-level operations
$service->getProductBySlug($slug);
$service->searchProducts($query, $filters);
$service->createProduct($data);

// Business logic
$service->calculatePrice($product, $quantity);
$service->checkAvailability($productId, $quantity);
$service->updateStock($productId, $stock, $reason);
```

## 🧪 Testing

### Unit Tests (Mock Repository)
```php
$mockRepo = Mockery::mock(ProductRepositoryInterface::class);
$mockRepo->shouldReceive('find')->andReturn($product);
$service = new ProductService($mockRepo);
```

### Feature Tests (Database)
```php
$repository = new ProductRepository();
$product = Product::factory()->create();
$result = $repository->findBySlug($product->slug);
```

## 🎯 Benefits

- **Separation of Concerns**: Repository = Data, Service = Business Logic
- **Testability**: Easy to mock and test independently
- **Maintainability**: Centralized logic, consistent patterns
- **Flexibility**: Can swap implementations easily
- **Caching**: Built-in caching in services
- **Reusability**: Services can be used across controllers

## 📦 File Locations

```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── ProductRepositoryInterface.php
│   │   └── CategoryRepositoryInterface.php
│   └── Services/
│       ├── ProductServiceInterface.php
│       └── CategoryServiceInterface.php
├── Repositories/
│   ├── BaseRepository.php
│   └── Product/
│       ├── ProductRepository.php
│       └── CategoryRepository.php
├── Services/
│   └── Product/
│       ├── ProductService.php
│       └── CategoryService.php
└── Providers/
    └── RepositoryServiceProvider.php

tests/
├── Unit/Services/
│   └── ProductServiceTest.php
└── Feature/Repositories/
    └── ProductRepositoryTest.php

docs/
├── repository-service-pattern.md
└── breadcrumb-component.md
```

## 🔧 Adding New Entities

1. Create repository interface & implementation
2. Create service interface & implementation  
3. Add bindings to RepositoryServiceProvider
4. Write tests
5. Use in controllers

Pattern ini memberikan foundation yang solid untuk scalable Laravel application! 🎉
