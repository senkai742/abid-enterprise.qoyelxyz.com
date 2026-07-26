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
        // Get the first company and branch for existing expense heads without global scope
        $firstCompany = \App\Models\Company::withoutGlobalScope('branch')->first();
        $firstBranch = \App\Models\Branch::withoutGlobalScope('branch')->first();

        if ($firstCompany && $firstBranch) {
            \App\Models\ExpenseHead::withoutGlobalScope('branch')
                ->whereNull('company_id')
                ->orWhereNull('branch_id')
                ->update([
                    'company_id' => $firstCompany->id,
                    'branch_id' => $firstBranch->id,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just updating existing data
    }
};
