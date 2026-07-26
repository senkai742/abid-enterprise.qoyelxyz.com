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
        Schema::create('salesreturn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesreturn_id');
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->decimal('purchase_price',20,2);
            $table->decimal('sell_price',20,2);
            $table->decimal('qnty',8);
            $table->decimal('total_price',20,2);
            $table->foreignId('customer_id');
            $table->foreignId('user_id');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesreturn_items');
    }
};
