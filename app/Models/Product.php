<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Scope;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'purchase_price',
        'sell_price',
        'quantity',
        'status',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected $appends = ['image_url'];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            if (!$user) {
                return; // Don't apply scope if no user is authenticated
            }
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if ($role == "admin") {
                $builder->where('products.company_id', $company_id);
            } else {
                $builder->where('products.company_id', $company_id)->where('products.branch_id', $branch_id);
            }
        });
    }

    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/img-placeholder.jpg');
    }

    public function getImageUrlAttribute()
    {
        return $this->getImageUrl();
    }

    public function branchStocks()
    {
        return $this->hasMany(\App\Models\BranchProductStock::class);
    }
    
    public function supplier()
    {
        return $this->hasOne(\App\Models\Supplier::class);
    }
}
