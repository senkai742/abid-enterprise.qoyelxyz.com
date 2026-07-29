<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SalesreturnItems extends Model
{
    protected $fillable =[
        'salesreturn_id',
        'order_id',
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'total_price',
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

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
