<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SalesreturnItemCart extends Model
{
   protected $fillable =[
        'purchase_price',
        'sell_price',
        'qnty',
        'total_price',
        'product_id',
        'order_id',
        'customer_id',
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


    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
