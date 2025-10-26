<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ancestor_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('descendant_id')->constrained('customers')->onDelete('cascade');
            $table->unsignedInteger('depth')->default(0); // 0 = same node
            $table->timestamps();

            $table->unique(['ancestor_id', 'descendant_id']);
            $table->index(['ancestor_id']);
            $table->index(['descendant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_networks');
    }
};
