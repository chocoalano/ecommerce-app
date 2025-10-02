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
         * Tabel: landings
         * Halaman landing promo/offer (misalnya campaign Galaxy Week).
         */
        Schema::create('landings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key halaman landing');
            $table->foreignId('promotion_id')->nullable()
                ->constrained('promotions')->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke promotions.id jika landing terkait promo');
            $table->string('slug', 255)->unique()->comment('Slug unik untuk URL landing page');
            $table->string('title', 255)->nullable()->comment('Judul utama landing page');
            $table->text('hero_image_url')->nullable()->comment('URL gambar hero/banner utama');
            $table->json('meta_json')->nullable()->comment('Metadata SEO/OG tags dalam JSON');
            $table->boolean('is_active')->default(true)->comment('Status aktif landing page');
            $table->timestamps();

            $table->comment('Master halaman landing campaign/promo');
        });

        /**
         * Tabel: landing_sections
         * Komponen/section dalam halaman landing (misalnya carousel, grid, FAQ).
         */
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key section landing');
            $table->foreignId('landing_id')
                ->constrained('landings')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke landings.id (halaman induk)');
            $table->string('type', 50)->comment('Jenis section: text, grid, carousel, faq, terms');
            $table->json('content_json')->nullable()->comment('Konten terstruktur section (JSON)');
            $table->integer('sort_order')->default(0)->comment('Urutan tampilan section dalam halaman');

            $table->comment('Daftar section dalam halaman landing');
        });

        /**
         * Tabel: tracking_utm
         * Menyimpan data parameter UTM untuk tracking campaign di landing.
         */
        Schema::create('tracking_utm', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key tracking UTM');
            $table->foreignId('landing_id')->nullable()
                ->constrained('landings')->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke landings.id (landing terkait)');
            $table->string('utm_source', 100)->nullable()->comment('Parameter UTM source (mis. google, fb)');
            $table->string('utm_medium', 100)->nullable()->comment('Parameter UTM medium (mis. cpc, banner)');
            $table->string('utm_campaign', 100)->nullable()->comment('Nama campaign UTM');
            $table->string('utm_content', 100)->nullable()->comment('Konten/adset UTM');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu data UTM tercatat');

            $table->comment('Tracking UTM campaign untuk landing pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_utm');
        Schema::dropIfExists('landing_sections');
        Schema::dropIfExists('landings');
    }
};
