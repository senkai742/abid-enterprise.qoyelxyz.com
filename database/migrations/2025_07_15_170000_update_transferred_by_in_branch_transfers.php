<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Get the first admin user
        $admin = DB::table('users')->where('role', 'admin')->first();
        if ($admin) {
            DB::table('branch_transfers')
                ->whereNull('transferred_by')
                ->update(['transferred_by' => $admin->id]);
        }
    }

    public function down()
    {
        // Optionally, set transferred_by back to null for these records
        // DB::table('branch_transfers')->update(['transferred_by' => null]);
    }
};
