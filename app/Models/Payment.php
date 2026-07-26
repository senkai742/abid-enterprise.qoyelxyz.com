<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'order_id',
        'user_id',
        'branch_id',
        'company_id',
    ];

    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class, 'order_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = auth()->user();
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
