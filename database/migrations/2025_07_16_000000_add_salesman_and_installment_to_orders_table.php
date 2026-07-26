<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add salesman_id field (references users table)
            $table->foreignId('salesman_id')->nullable()->after('user_id');
            $table->foreign('salesman_id')->references('id')->on('users')->onDelete('set null');

            // Add installment plan fields
            $table->string('installment_plan')->nullable()->after('salesman_id'); // '3', '6', '12' months
            $table->decimal('installment_amount', 10, 2)->nullable()->after('installment_plan'); // Monthly payment amount
            $table->date('installment_start_date')->nullable()->after('installment_amount'); // When installment starts
            $table->enum('installment_status', ['active', 'completed', 'cancelled'])->default('active')->after('installment_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->dropColumn(['salesman_id', 'installment_plan', 'installment_amount', 'installment_start_date', 'installment_status']);
        });
    }
};
