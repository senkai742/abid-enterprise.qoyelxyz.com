<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'total_qnty',
        'total_amount',
        'return_amount',
        'profit_amount',
        'notes',
        'user_id',
        'branch_id',
        'company_id',
    ];


    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::check() ? Auth::user() : null;
            if (!$user) return;
            $builder->where('company_id', $user->company_id);
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItems::class, 'purchase_return_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function getCustomerName()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }
}
