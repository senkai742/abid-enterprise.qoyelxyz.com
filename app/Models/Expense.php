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
            if (!$user) return;
            $builder->where('company_id', $user->company_id);
        });
    }
}
