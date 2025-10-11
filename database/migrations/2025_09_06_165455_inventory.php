<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Tabel: inventory_locations
         * Daftar lokasi gudang / titik penyimpanan stok.
         */
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key lokasi gudang');
            $table->string('code', 50)->unique()->comment('Kode unik lokasi gudang (mis. WH-JKT-01)');
            $table->string('name', 255)->nullable()->comment('Nama lokasi gudang');
            $table->string('street', 255)->nullable()->comment('Nama jalan/alamat detail');
            $table->string('city', 100)->nullable()->comment('Kota');
            $table->string('province', 100)->nullable()->comment('Provinsi');
            $table->string('postal_code', 20)->nullable()->comment('Kode pos');
            $table->string('country', 100)->nullable()->comment('Negara');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Koordinat latitude');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Koordinat longitude');
            $table->boolean('is_active')->default(true)->index()->comment('Status aktif/tidak aktif lokasi');
            $table->timestamps(); // created_at & updated_at
            $table->comment('Master data lokasi gudang / titik inventory');
        });

        /**
         * Tabel: inventories
         * Stok per produk di tiap lokasi gudang (tanpa variant).
         */
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key inventory');

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk');

            $table->foreignId('location_id')
                ->constrained('inventory_locations')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke lokasi gudang');

            $table->unsignedInteger('qty_on_hand')->default(0)->comment('Jumlah stok tersedia (fisik) di gudang');
            $table->unsignedInteger('qty_reserved')->default(0)->comment('Jumlah stok yang sedang dipesan/direservasi');
            $table->unsignedInteger('safety_stock')->default(0)->comment('Batas minimal stok (safety stock)');

            $table->timestamps(); // agar mudah audit & sinkronisasi

            $table->unique(['product_id','location_id'], 'inventories_product_location_unique');
            $table->index(['location_id', 'product_id']);
            $table->comment('Stok produk per lokasi gudang');
        });

        /**
         * Tabel: stock_movements
         * Catatan pergerakan stok (IN, OUT, RESERVE, RELEASE, ADJUST) per produk.
         */
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key pergerakan stok');

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk yang berubah stoknya');

            $table->foreignId('location_id')->nullable()
                ->constrained('inventory_locations')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke lokasi gudang terkait pergerakan');

            $table->string('type', 30)->comment('Jenis pergerakan stok: IN, OUT, RESERVE, RELEASE, ADJUST');
            $table->integer('qty')->comment('Jumlah unit yang bergerak (boleh negatif/positif sesuai tipe)');

            // Referensi transaksi eksternal (opsional)
            $table->string('ref_type', 50)->nullable()->comment('Tipe referensi (ORDER, RETURN, MANUAL, dsb.)');
            $table->unsignedBigInteger('ref_id')->nullable()->comment('ID referensi transaksi terkait (mis. order_id)');

            $table->string('note', 255)->nullable()->comment('Catatan tambahan');
            $table->timestamps(); // created_at & updated_at

            // Indeks untuk query histori
            $table->index(['product_id', 'location_id', 'created_at'], 'stock_movements_prod_loc_time_idx');

            $table->comment('Log historis pergerakan stok per produk');
        });

        /**
         * (Opsional) Tambahkan CHECK constraint untuk type jika DB mendukung.
         * MySQL 8.0+ menghormati CHECK; untuk versi lama, abaikan/handle di aplikasi.
         */
        try {
            DB::statement("
                ALTER TABLE stock_movements
                ADD CONSTRAINT chk_stock_movements_type
                CHECK (type IN ('IN','OUT','RESERVE','RELEASE','ADJUST'))
            ");
        } catch (\Throwable $e) {
            // Abaikan jika engine tidak mendukung CHECK; pastikan validasi di level aplikasi.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventory_locations');
    }
};
