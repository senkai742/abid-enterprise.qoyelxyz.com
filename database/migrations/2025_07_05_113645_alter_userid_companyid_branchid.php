<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     
       
        $tableName = "customers";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });
      

        $tableName = "damage_items";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "expenses";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "expense_heads";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "payments";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "products";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchases";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchase_cart";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchase_items";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchase_returns";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchase_return_items";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "purchase_return_item_carts";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "sales";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "salesreturns";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "salesreturn_items";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "salesreturn_item_carts";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "sale_items";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "settings";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "suppliers";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "supplier_payments";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "users";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

        $tableName = "user_cart";

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches');
            }
            if (!Schema::hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies');
            }
        });

         

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
