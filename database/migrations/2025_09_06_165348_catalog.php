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
         * Tabel: categories
         * Hierarki kategori produk (bisa nested).
         */
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key kategori');
            $table->foreignId('parent_id')->nullable()
                ->constrained('categories')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke parent kategori (nested)');
            $table->string('slug', 255)->unique()->comment('Slug unik kategori untuk URL');
            $table->string('name', 255)->comment('Nama kategori');
            $table->text('description')->nullable()->comment('Deskripsi kategori');
            $table->integer('sort_order')->default(0)->comment('Urutan tampil kategori');
            $table->boolean('is_active')->default(true)->comment('Status aktif kategori');
            $table->string('image', 255)->default('storage/images/galaxy-z-flip7-share-image.png')->comment('gambar kategori');
            $table->timestamps();
            $table->comment('Daftar kategori produk');
        });

        /**
         * Tabel: products
         * Master produk (belum termasuk variasi/variant).
         */
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key produk');
            $table->string('sku', 100)->unique()->comment('Kode unik produk (Stock Keeping Unit)');
            $table->string('slug', 255)->unique()->comment('Slug unik produk untuk URL');
            $table->string('name', 255)->comment('Nama produk');
            $table->string('short_desc', 500)->nullable()->comment('Deskripsi singkat');
            $table->longText('long_desc')->nullable()->comment('Deskripsi panjang produk');
            $table->string('brand', 100)->nullable()->comment('Merek/brand produk');
            $table->integer('warranty_months')->nullable()->comment('Garansi dalam bulan, null jika tidak ada');
            $table->boolean('is_active')->default(true)->comment('Status aktif produk');
            $table->timestamps();
            $table->comment('Master produk utama');
        });

        /**
         * Tabel: product_categories
         * Pivot many-to-many antara produk & kategori.
         */
        Schema::create('product_categories', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke kategori');
            $table->unique(['product_id','category_id'], 'product_categories_unique');
            $table->comment('Relasi produk dengan beberapa kategori');
        });

        /**
         * Tabel: product_media
         * Media (gambar/video) terkait produk utama (bukan variant).
         */
        Schema::create('product_media', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key media produk');
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk');
            $table->text('url')->comment('URL file media');
            $table->string('type', 50)->nullable()->comment('Tipe media: image, video, 3d');
            $table->string('alt_text', 255)->nullable()->comment('Alt text SEO / aksesibilitas');
            $table->integer('sort_order')->default(0)->comment('Urutan tampil media');
            $table->boolean('is_primary')->default(false)->comment('Penanda media utama');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu dibuat');
            $table->comment('Media (gambar/video) untuk produk');
        });

        /**
         * Tabel: product_variants
         * Variasi produk (warna, ukuran, storage, dll).
         */
        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key variant');
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk utama');
            $table->string('variant_sku', 120)->unique()->comment('SKU unik untuk variant');
            $table->string('name', 255)->nullable()->comment('Nama variant (mis. Warna/Storage)');
            $table->json('attributes_json')->nullable()->comment('Atribut variant dalam JSON (mis. {"color":"Black"})');
            $table->decimal('base_price', 18, 2)->comment('Harga dasar variant');
            $table->string('currency', 3)->nullable()->comment('Kode mata uang (mis. IDR)');
            $table->integer('weight_gram')->nullable()->comment('Berat (gram)');
            $table->integer('length_mm')->nullable()->comment('Panjang (mm)');
            $table->integer('width_mm')->nullable()->comment('Lebar (mm)');
            $table->integer('height_mm')->nullable()->comment('Tinggi (mm)');
            $table->boolean('is_active')->default(true)->comment('Status aktif variant');
            $table->integer('stock')->nullable()->comment('Persediaan stok variant');
            $table->timestamps();
            $table->comment('Daftar variasi produk');
        });

        /**
         * Tabel: product_variant_media
         * Media (gambar/video) khusus untuk variant produk.
         */
        Schema::create('product_variant_media', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key media variant');
            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke variant produk');
            $table->text('url')->comment('URL file media variant');
            $table->string('type', 50)->nullable()->comment('Tipe media: image, video, 3d');
            $table->string('alt_text', 255)->nullable()->comment('Alt text media');
            $table->integer('sort_order')->default(0)->comment('Urutan tampil media');
            $table->boolean('is_primary')->default(false)->comment('Penanda media utama untuk variant');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu dibuat');
            $table->comment('Media untuk variant produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutkan drop agar FK tidak bentrok
        Schema::dropIfExists('product_variant_media');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
