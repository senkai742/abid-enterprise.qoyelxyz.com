<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'amount',
        'purchase_id',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted() {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = auth()->user();
            if (!$user) return;
            $builder->where('company_id', $user->company_id);
        });
    }

    public function purchase()
    {
        return $this->belongsTo(\App\Models\Purchase::class, 'purchase_id');
    }
}
