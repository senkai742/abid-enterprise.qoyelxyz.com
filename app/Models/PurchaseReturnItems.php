<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItems extends Model
{
    protected $fillable =[
        'purchase_return_id',
        'purchase_id',
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'supplier_id',
        'user_id',
        'branch_id',
        'company_id',
    ];


    protected static function booted() {
        static::addGlobalScope('branch', function (Builder $builder) {
             $user = auth()->user();
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if($role=="admin"){
                $builder->where('company_id', $company_id);
            }else{
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
