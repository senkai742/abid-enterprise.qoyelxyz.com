<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'avatar',
        'balance',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            if (!$user) return;
            $builder->where('company_id', $user->company_id);
        });
    }

    // Define relationships here (e.g., with the Product model)
}
