<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\Product\ProductRepository;
use App\Models\Product\Product;
use App\Models\Product\Category;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    /** @test */
    public function it_can_find_product_by_slug()
    {
        // Arrange
        $product = Product::factory()->create([
            'slug' => 'test-product-slug',
            'name' => 'Test Product'
        ]);

        // Act
        $result = $this->repository->findBySlug('test-product-slug');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
        $this->assertEquals('Test Product', $result->name);
    }

    /** @test */
    public function it_returns_null_for_non_existent_slug()
    {
        // Act
        $result = $this->repository->findBySlug('non-existent-slug');

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_find_product_by_sku()
    {
        // Arrange
        $product = Product::factory()->create([
            'sku' => 'TEST-SKU-123',
            'name' => 'Test Product'
        ]);

        // Act
        $result = $this->repository->findBySku('TEST-SKU-123');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    /** @test */
    public function it_can_get_active_products_only()
    {
        // Arrange
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        // Act
        $result = $this->repository->getActive();

        // Assert
        $this->assertEquals(2, $result->count());
        $result->each(function ($product) {
            $this->assertTrue($product->is_active);
        });
    }

    /** @test */
    public function it_can_get_products_by_category()
    {
        // Arrange
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['is_active' => true]);
        $product2 = Product::factory()->create(['is_active' => true]);
        $product3 = Product::factory()->create(['is_active' => true]);

        // Associate products with category
        $product1->categories()->attach($category);
        $product2->categories()->attach($category);

        // Act
        $result = $this->repository->getByCategory($category->id, 10);

        // Assert
        $this->assertEquals(2, $result->total());
        $productIds = collect($result->items())->pluck('id');
        $this->assertTrue($productIds->contains($product1->id));
        $this->assertTrue($productIds->contains($product2->id));
        $this->assertFalse($productIds->contains($product3->id));
    }

    /** @test */
    public function it_can_search_products()
    {
        // Arrange
        Product::factory()->create([
            'name' => 'Laptop Gaming ASUS',
            'is_active' => true
        ]);
        Product::factory()->create([
            'name' => 'Mouse Gaming Razer',
            'is_active' => true
        ]);
        Product::factory()->create([
            'name' => 'Smartphone Samsung',
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->search('Gaming', [], 10);

        // Assert
        $this->assertEquals(2, $result->total());
    }

    /** @test */
    public function it_can_search_products_with_filters()
    {
        // Arrange
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'name' => 'Laptop Gaming',
            'base_price' => 1000,
            'brand' => 'ASUS',
            'is_active' => true
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Gaming Mouse',
            'base_price' => 50,
            'brand' => 'Razer',
            'is_active' => true
        ]);

        $product1->categories()->attach($category);

        $filters = [
            'categories' => [$category->id],
            'min_price' => 500,
            'max_price' => 1500,
            'brand' => ['ASUS']
        ];

        // Act
        $result = $this->repository->search('Gaming', $filters, 10);

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals($product1->id, collect($result->items())->first()->id);
    }

    /** @test */
    public function it_can_get_products_by_price_range()
    {
        // Arrange
        Product::factory()->create(['base_price' => 50, 'is_active' => true]);
        Product::factory()->create(['base_price' => 150, 'is_active' => true]);
        Product::factory()->create(['base_price' => 250, 'is_active' => true]);

        // Act
        $result = $this->repository->getByPriceRange(100, 200, 10);

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals(150, collect($result->items())->first()->base_price);
    }

    /** @test */
    public function it_can_get_related_products()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);
        $relatedProduct1 = Product::factory()->create(['is_active' => true]);
        $relatedProduct2 = Product::factory()->create(['is_active' => true]);
        $unrelatedProduct = Product::factory()->create(['is_active' => true]);

        // Associate products with same category
        $product->categories()->attach($category);
        $relatedProduct1->categories()->attach($category);
        $relatedProduct2->categories()->attach($category);

        // Act
        $result = $this->repository->getRelated($product, 5);

        // Assert
        $this->assertEquals(2, $result->count());
        $this->assertFalse($result->contains($product)); // Original product should not be included
        $this->assertTrue($result->contains($relatedProduct1));
        $this->assertTrue($result->contains($relatedProduct2));
        $this->assertFalse($result->contains($unrelatedProduct));
    }

    /** @test */
    public function it_can_update_stock()
    {
        // Arrange
        $product = Product::factory()->create(['stock' => 10]);
        $newStock = 25;

        // Act
        $result = $this->repository->updateStock($product->id, $newStock);

        // Assert
        $this->assertTrue($result);
        $product->refresh();
        $this->assertEquals($newStock, $product->stock);
    }

    /** @test */
    public function it_can_get_low_stock_products()
    {
        // Arrange
        Product::factory()->create(['stock' => 5, 'is_active' => true]);
        Product::factory()->create(['stock' => 8, 'is_active' => true]);
        Product::factory()->create(['stock' => 15, 'is_active' => true]);
        Product::factory()->create(['stock' => 0, 'is_active' => true]); // Out of stock

        // Act
        $result = $this->repository->getLowStock(10);

        // Assert
        $this->assertEquals(2, $result->count()); // Only products with stock > 0 and <= 10
        $result->each(function ($product) {
            $this->assertGreaterThan(0, $product->stock);
            $this->assertLessThanOrEqual(10, $product->stock);
        });
    }

    /** @test */
    public function it_can_chain_query_methods()
    {
        // Arrange
        Product::factory()->create(['name' => 'Product A', 'is_active' => true]);
        Product::factory()->create(['name' => 'Product B', 'is_active' => true]);
        Product::factory()->create(['name' => 'Product C', 'is_active' => false]);

        // Act
        $result = $this->repository
            ->where('is_active', true)
            ->orderBy('name', 'desc')
            ->limit(1)
            ->all();

        // Assert
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Product B', $result->first()->name);
    }

    /** @test */
    public function it_can_bulk_update_stock()
    {
        // Arrange
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 20]);

        $updates = [
            ['id' => $product1->id, 'stock' => 15],
            ['id' => $product2->id, 'stock' => 25]
        ];

        // Act
        $result = $this->repository->bulkUpdateStock($updates);

        // Assert
        $this->assertTrue($result);

        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(15, $product1->stock);
        $this->assertEquals(25, $product2->stock);
    }
}
