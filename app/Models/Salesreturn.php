<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Salesreturn extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'total_qnty',
        'total_amount',
        'return_amount',
        'profit_amount',
        'user_id',
        'notes',
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
                // Admin sees records from their company OR records with no company assigned
                $builder->where(function($query) use ($company_id) {
                    $query->where('company_id', $company_id)
                          ->orWhereNull('company_id');
                });
            }else{
                $builder->where(function($query) use ($company_id, $branch_id) {
                    $query->where('company_id', $company_id)
                          ->orWhereNull('company_id');
                })->where(function($query) use ($branch_id) {
                    $query->where('branch_id', $branch_id)
                          ->orWhereNull('branch_id');
                });
            }
        });
    }

    public function items()
    {
        return $this->hasMany(SalesreturnItems::class,'salesreturn_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function getCustomerName()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }
}
