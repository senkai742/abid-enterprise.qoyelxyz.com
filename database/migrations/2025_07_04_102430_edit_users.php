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
        Schema::table('users',function(Blueprint $table){
            $table->string('role',191)->default('admin')->after('email');
            $table->string('mobile',191)->nullable()->after('role');
            $table->string('address',191)->nullable()->after('mobile');
            $table->foreignId('branch_id')->nullable()->after('address');
            $table->foreignId('company_id')->nullable()->after('branch_id');
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
