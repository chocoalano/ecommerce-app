<?php

namespace Database\Seeders;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\StockMovement;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 2 gudang aktif
        $locA = InventoryLocation::factory()->create(['code' => 'WH-JKT-01']);
        $locB = InventoryLocation::factory()->create(['code' => 'WH-SBY-01']);

        // Buat 5 produk dengan stok awal di dua lokasi
        Product::factory()->count(5)->create()->each(function ($product) use ($locA, $locB) {
            Inventory::factory()->for($product)->create(['location_id' => $locA->id]);
            Inventory::factory()->for($product)->create(['location_id' => $locB->id]);

            // Tambah movement masuk 10 unit ke WH-JKT-01
            StockMovement::factory()
                ->forProduct($product->id)
                ->atLocation($locA)
                ->type(StockMovement::TYPE_IN)
                ->state(['qty' => 10])
                ->create();
        });

    }
}
