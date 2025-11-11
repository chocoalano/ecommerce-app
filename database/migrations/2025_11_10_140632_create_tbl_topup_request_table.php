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
        Schema::create('tbl_topup_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50); // transfer_bank, va, ewallet, etc
            $table->string('payment_proof')->nullable(); // path to uploaded proof
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('customers')->nullOnDelete();

            $table->index(['member_id', 'status']);
            $table->index('created_at');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_topup_request');
    }
};
