<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InstallmentPayment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_date',
        'status',
        'notes',
        'collected_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = auth()->user();
            if ($user) {
                $builder->whereHas('order', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                    if ($user->role !== 'admin') {
                        $query->where('branch_id', $user->branch_id);
                    }
                });
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Sale::class, 'order_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }
}
