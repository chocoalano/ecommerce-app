<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class CategoryProoductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kategori root + child
        $electronics = Category::factory()->create(['name' => 'Elektronik']);
        Category::factory()->childOf($electronics)->count(3)->create();

        // Produk lengkap dengan kategori & media
        Product::factory()
            ->count(10)
            ->withCategories(2) // auto attach 2 kategori
            ->withMedia(3)      // buat 3 gambar, satu primary
            ->create();
    }
}
