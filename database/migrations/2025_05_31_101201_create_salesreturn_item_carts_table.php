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
        Schema::create('salesreturn_item_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesreturn_id')->nullable();
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->decimal('purchase_price',20,2)->nullable();
            $table->decimal('sell_price',20,2)->nullable();
            $table->decimal('qnty',8);
            $table->decimal('total_price',20,2);
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesreturn_item_carts');
    }
};
