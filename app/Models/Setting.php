<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted()
    {
        // static::addGlobalScope('branch', function (Builder $builder) {
             
        //         $user = auth()->user();
        //         $builder->where('company_id', $user->company_id)
        //                 ->where('branch_id', $user->branch_id);
           
        // });
    }

}
