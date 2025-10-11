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
            $table->integer('sort_order')->default(0)->index()->comment('Urutan tampil kategori');
            $table->boolean('is_active')->default(true)->index()->comment('Status aktif kategori');
            $table->string('image', 255)->default('storage/images/galaxy-z-flip7-share-image.png')->comment('Gambar kategori');
            $table->timestamps();
            $table->comment('Daftar kategori produk');
        });

        /**
         * Tabel: products
         * Master produk tunggal (tanpa variasi).
         */
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key produk');

            // Identitas & konten
            $table->string('sku', 100)->unique()->comment('Kode unik produk (Stock Keeping Unit)');
            $table->string('slug', 255)->unique()->comment('Slug unik produk untuk URL');
            $table->string('name', 255)->comment('Nama produk');
            $table->string('short_desc', 500)->nullable()->comment('Deskripsi singkat');
            $table->longText('long_desc')->nullable()->comment('Deskripsi panjang produk');
            $table->string('brand', 100)->nullable()->comment('Merek/brand produk');
            $table->integer('warranty_months')->nullable()->comment('Garansi dalam bulan, null jika tidak ada');

            // Harga & stok (dipindah dari product_variants)
            $table->decimal('base_price', 18, 2)->default(0)->comment('Harga dasar produk');
            $table->char('currency', 3)->default('IDR')->comment('Kode mata uang (mis. IDR)');
            $table->unsignedInteger('stock')->default(0)->comment('Persediaan stok produk');

            // Dimensi & berat (opsional)
            $table->unsignedInteger('weight_gram')->nullable()->comment('Berat (gram)');
            $table->unsignedInteger('length_mm')->nullable()->comment('Panjang (mm)');
            $table->unsignedInteger('width_mm')->nullable()->comment('Lebar (mm)');
            $table->unsignedInteger('height_mm')->nullable()->comment('Tinggi (mm)');

            // Status
            $table->boolean('is_active')->default(true)->index()->comment('Status aktif produk');

            $table->timestamps();
            $table->comment('Master produk utama tanpa variasi');
        });

        /**
         * Tabel: product_categories
         * Pivot many-to-many antara produk & kategori.
         */
        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key pivot');
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke produk');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke kategori');

            $table->unique(['product_id','category_id'], 'product_categories_unique');
            $table->index('product_id');
            $table->index('category_id');

            $table->comment('Relasi produk dengan beberapa kategori');
        });

        /**
         * Tabel: product_media
         * Media (gambar/video) terkait produk.
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

            $table->timestamps();
            $table->index(['product_id', 'sort_order']);

            $table->comment('Media (gambar/video) untuk produk');
        });

        /**
         * CATATAN:
         * - Jika ingin membatasi hanya satu media utama per produk, gunakan validasi di aplikasi,
         *   atau buat partial unique index di DB yang mensyaratkan is_primary=1 (fitur ini tergantung engine DB).
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutkan drop agar FK tidak bentrok
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
