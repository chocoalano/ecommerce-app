<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use App\Services\Product\ProductService;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductServiceTest extends TestCase
{
    protected ProductService $productService;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->mockRepository);

        // Clear cache for each test
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_product_by_slug()
    {
        // Arrange
        $slug = 'test-product';
        $expectedProduct = new Product([
            'id' => 1,
            'name' => 'Test Product',
            'slug' => $slug,
            'base_price' => 100.00
        ]);

        $this->mockRepository
            ->shouldReceive('with')
            ->once()
            ->with([
                'categories',
                'media',
                'primaryMedia',
                'reviews.user',
                'promotions' => Mockery::type('Closure')
            ])
            ->andReturnSelf();

        $this->mockRepository
            ->shouldReceive('findBySlug')
            ->once()
            ->with($slug)
            ->andReturn($expectedProduct);

        // Act
        $result = $this->productService->getProductBySlug($slug);

        // Assert
        $this->assertEquals($expectedProduct, $result);
    }

    /** @test */
    public function it_can_search_products()
    {
        // Arrange
        $query = 'laptop';
        $filters = ['categories' => [1, 2]];
        $perPage = 15;

        $expectedResults = new \Illuminate\Pagination\LengthAwarePaginator(
            new Collection([new Product()]),
            1,
            $perPage,
            1
        );

        $this->mockRepository
            ->shouldReceive('search')
            ->once()
            ->with($query, $filters, $perPage)
            ->andReturn($expectedResults);

        // Act
        $result = $this->productService->searchProducts($query, $filters, $perPage);

        // Assert
        $this->assertEquals($expectedResults, $result);
    }

    /** @test */
    public function it_can_calculate_price_with_promotions()
    {
        // Arrange
        $product = new Product([
            'base_price' => 100.00
        ]);

        // Mock promotions collection
        $promotions = new Collection([
            (object) [
                'pivot' => (object) [
                    'min_qty' => 1,
                    'discount_percent' => 10,
                    'discount_value' => 0
                ]
            ]
        ]);

        $product->setRelation('promotions', $promotions);
        $quantity = 2;

        // Act
        $result = $this->productService->calculatePrice($product, $quantity);

        // Assert
        $this->assertEquals(100.00, $result['base_price']);
        $this->assertEquals(200.00, $result['total_base_price']);
        $this->assertEquals(20.00, $result['discount']);
        $this->assertEquals(180.00, $result['final_price']);
        $this->assertEquals(2, $result['quantity']);
    }

    /** @test */
    public function it_can_check_product_availability()
    {
        // Arrange
        $productId = 1;
        $quantity = 5;
        $product = new Product([
            'id' => $productId,
            'stock' => 10,
            'is_active' => true
        ]);

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with($productId)
            ->andReturn($product);

        // Act
        $result = $this->productService->checkAvailability($productId, $quantity);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_for_insufficient_stock()
    {
        // Arrange
        $productId = 1;
        $quantity = 15;
        $product = new Product([
            'id' => $productId,
            'stock' => 10,
            'is_active' => true
        ]);

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with($productId)
            ->andReturn($product);

        // Act
        $result = $this->productService->checkAvailability($productId, $quantity);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_false_for_inactive_product()
    {
        // Arrange
        $productId = 1;
        $quantity = 5;
        $product = new Product([
            'id' => $productId,
            'stock' => 10,
            'is_active' => false
        ]);

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with($productId)
            ->andReturn($product);

        // Act
        $result = $this->productService->checkAvailability($productId, $quantity);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_featured_products_with_caching()
    {
        // Arrange
        $limit = 12;
        $expectedProducts = new Collection([
            new Product(['id' => 1, 'name' => 'Featured Product 1']),
            new Product(['id' => 2, 'name' => 'Featured Product 2']),
        ]);

        $this->mockRepository
            ->shouldReceive('getFeatured')
            ->once()
            ->with($limit)
            ->andReturn($expectedProducts);

        // Act - First call should hit repository
        $result1 = $this->productService->getFeaturedProducts($limit);

        // Act - Second call should hit cache (repository shouldn't be called again)
        $result2 = $this->productService->getFeaturedProducts($limit);

        // Assert
        $this->assertEquals($expectedProducts, $result1);
        $this->assertEquals($expectedProducts, $result2);
    }

    /** @test */
    public function it_can_update_stock_with_logging()
    {
        // Arrange
        $productId = 1;
        $newStock = 50;
        $reason = 'Restock';

        $product = new Product([
            'id' => $productId,
            'stock' => 25,
            'slug' => 'test-product'
        ]);

        $this->mockRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with($productId)
            ->andReturn($product);

        $this->mockRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($productId, $newStock)
            ->andReturn(true);

        // Act
        $result = $this->productService->updateStock($productId, $newStock, $reason);

        // Assert
        $this->assertTrue($result);
    }
}
