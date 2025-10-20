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
        Schema::create('footer_socials', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // Instagram, Facebook, TikTok, YouTube, etc
            $table->string('url');
            $table->string('icon')->nullable(); // SVG or icon class
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_socials');
    }
};
