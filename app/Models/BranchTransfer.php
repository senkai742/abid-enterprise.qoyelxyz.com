<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'from_branch_id',
        'to_branch_id',
        'transferred_by',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
