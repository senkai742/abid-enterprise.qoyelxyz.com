<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserCart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'customer_id',
        'branch_id',
        'company_id',
    ];


    // Removed global scope to prevent interference with cart operations
    // Cart operations should be handled at the controller level with proper scoping


    public function balance()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
