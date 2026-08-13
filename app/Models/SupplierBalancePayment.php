<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupplierBalancePayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'amount',
        'note',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $user = auth()->user();
            if (!$user) return;
            $builder->where('company_id', $user->company_id);
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
