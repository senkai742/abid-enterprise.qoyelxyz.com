<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'branch_id',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];




    public function cart()
    {
        return $this->belongsToMany(Product::class, 'user_cart')->withPivot('quantity', 'customer_id', 'branch_id', 'company_id', 'user_id', 'sell_price');
    }

    public function purchaseCart(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'purchase_cart')
            ->withPivot('qnty', 'supplier_id', 'supplier_invoice_id', 'purchase_price', 'sell_price', 'branch_id', 'company_id')
            ->withTimestamps();
    }

    public function salesreturnCart()
    {
        return $this->belongsToMany(Product::class, 'salesreturn_item_carts', 'user_id', 'product_id')
            ->withPivot('id', 'qnty', 'sell_price', 'total_price', 'order_id', 'customer_id', 'branch_id', 'company_id')
            ->withTimestamps();
    }


    public function getFullname()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function company_id()
    {
        return $this->company_id;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function branch_name()
    {
        return Branch::where('id', $this->id)->first()->branch_name;
    }


    public function getAvatar()
    {
        return 'https://www.gravatar.com/avatar/' . md5($this->email);
    }
}
