<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable =[
        'purchase_price',
        'sell_price',
        'quantity', // Match the existing table column name
        'product_id',
        'sale_id',
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
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
