<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PurchaseCart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'supplier_id',
        'supplier_invoice_id',
        'qnty',
        'purchase_price',
        'sell_price',
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

    public function balance(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}
