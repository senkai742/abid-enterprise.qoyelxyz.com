<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'company_type',
        'total_branch',
        'address',
        'mobile',
        'admin_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            if (!$user) {
                return; // Don't apply scope if no user is authenticated
            }
            $builder->where('user_id', $user->id);
        });
    }
}
