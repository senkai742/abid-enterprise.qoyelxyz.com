<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_cart', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_cart', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });
    }
};
