<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'avatar',
        'customer',
        'balance',
        'user_id',
        'branch_id',
        'company_id',
    ];

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
                $builder->where('company_id', $company_id);
            } else {
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }

    public function getAvatarUrl()
    {
        return Storage::url($this->avatar);
    }

    public function orders()
    {
        return $this->hasMany(Sale::class, 'customer_id')->with('items');
    }

    public function orderLists()
    {
        return $this->hasManyThrough(SaleItem::class, Sale::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Sale::class);
    }
}
