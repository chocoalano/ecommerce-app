<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->json('address_json')->nullable()->comment('Alamat lengkap dalam format JSON (jalan, kota, koordinat, dsb.)');
            $table->boolean('is_active')->default(true)->comment('Status aktif/tidak aktif lokasi');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu pencatatan lokasi');
            $table->comment('Master data lokasi gudang / titik inventory');
        });

        /**
         * Tabel: inventories
         * Stok per variant produk di tiap lokasi gudang.
         */
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key inventory');
            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke variant produk');
            $table->foreignId('location_id')
                ->constrained('inventory_locations')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke lokasi gudang');
            $table->integer('qty_on_hand')->default(0)->comment('Jumlah stok tersedia (fisik) di gudang');
            $table->integer('qty_reserved')->default(0)->comment('Jumlah stok yang sedang dipesan/direservasi');
            $table->integer('safety_stock')->default(0)->comment('Batas minimal stok (safety stock)');
            $table->timestamp('updated_at')->useCurrent()->comment('Terakhir kali stok diperbarui');
            $table->unique(['variant_id','location_id'], 'inventories_variant_location_unique');
            $table->comment('Stok produk per variant di setiap lokasi');
        });

        /**
         * Tabel: stock_movements
         * Catatan pergerakan stok (IN, OUT, RESERVE, RELEASE, ADJUST).
         */
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key pergerakan stok');
            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke variant produk yang berubah stoknya');
            $table->foreignId('location_id')->nullable()
                ->constrained('inventory_locations')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke lokasi gudang terkait pergerakan');
            $table->string('type', 30)->comment('Jenis pergerakan stok: IN, OUT, RESERVE, RELEASE, ADJUST');
            $table->integer('qty')->comment('Jumlah unit yang bergerak (positif/negatif)');
            $table->string('ref_type', 50)->nullable()->comment('Tipe referensi (ORDER, RETURN, MANUAL, dsb.)');
            $table->unsignedBigInteger('ref_id')->nullable()->comment('ID referensi transaksi terkait (mis. order_id)');
            $table->string('note', 255)->nullable()->comment('Catatan tambahan');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu pencatatan pergerakan');
            $table->comment('Log historis pergerakan stok');
        });
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
