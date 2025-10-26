<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id')->unique('code');
            $table->foreignId('parent_id')->nullable()->after('code')->constrained('customers')->nullOnDelete();
            $table->foreignId('sponsor_id')->nullable()->after('parent_id')->constrained('customers')->nullOnDelete();
            $table->enum('position', ['left', 'right'])->nullable()->after('sponsor_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['position']);
            $table->dropConstrainedForeignId('sponsor_id');
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['code']);
        });
    }
};
