<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'source',
        'sale_id',
        'user_id',
        'branch_id',
        'company_id',
    ];

    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class, 'sale_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = auth()->user();
            if (!$user) return;
            $builder->where($builder->getModel()->getTable() . '.company_id', $user->company_id);
        });
    }
}
