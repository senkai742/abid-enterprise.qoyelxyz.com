<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Purchase extends Model
{
     protected $fillable = [
        'supplier_id',
        'invoice_no',
        'sub_total',
        'discount_amount',
        'gr_total',
        'paid_amount',
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

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}
