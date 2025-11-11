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
        Schema::create('tbl_withdrawal_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->decimal('amount', 15, 2);
            $table->string('withdrawal_method', 50); // transfer_bank, ewallet, etc
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_name', 100);
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('customers')->nullOnDelete();

            $table->index(['member_id', 'status']);
            $table->index('created_at');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_withdrawal_request');
    }
};
