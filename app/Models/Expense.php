<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Expense extends Model
{
    protected $fillable = [
        'expense_head',
        'expense_description',
        'expense_amount',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            if (!$user) {
                return; // Don't apply scope if no user is authenticated
            }
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if ($role == "admin") {
                $builder->where('company_id', $company_id);
            } else {
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }
}
