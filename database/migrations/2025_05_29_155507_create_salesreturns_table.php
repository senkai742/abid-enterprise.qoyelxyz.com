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
        Schema::create('salesreturns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreignId('order_id');
            $table->decimal('total_qnty',8);
            $table->decimal('total_amount',20,2);
            $table->decimal('return_amount',20,2);
            $table->decimal('profit_amount',20,2);
            $table->foreignId('user_id');
            $table->text('notes')->nullable();
            $table->timestamps();
 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesreturns');
    }
};
