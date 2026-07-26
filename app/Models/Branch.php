<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'address',
        'mobile',
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
            $builder->where('company_id', $company_id);
        });
    }

    public function productStocks()
    {
        return $this->hasMany(\App\Models\BranchProductStock::class);
    }
}
