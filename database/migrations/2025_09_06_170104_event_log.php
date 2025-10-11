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
         * Tabel: event_logs
         * Menyimpan jejak aktivitas (audit log) untuk keperluan monitoring & debugging.
         */
        Schema::create('event_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key log');
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete()->cascadeOnUpdate()
                ->comment('User yang memicu event (nullable jika sistem/guest)');
            $table->string('event_type', 100)->nullable()->comment('Jenis event (mis. LOGIN, ORDER_PLACED, PAYMENT_FAILED)');
            $table->string('entity', 100)->nullable()->comment('Nama entitas terkait event (mis. orders, payments)');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID entitas terkait event');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu event tercatat');

            $table->comment('Audit log untuk menyimpan riwayat event & aktivitas sistem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
