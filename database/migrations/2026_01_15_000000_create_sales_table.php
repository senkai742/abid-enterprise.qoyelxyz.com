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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('sub_total', 20, 2)->nullable();
            $table->decimal('discount_amount', 20, 2)->nullable();
            $table->decimal('gr_total', 20, 2)->nullable();
            $table->decimal('paid_amount', 20, 2)->nullable();
            $table->decimal('profit_amount', 20, 2)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('installment_plan')->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->date('installment_start_date')->nullable();
            $table->enum('installment_status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
