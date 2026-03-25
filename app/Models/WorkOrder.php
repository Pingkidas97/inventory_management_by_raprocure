<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    protected $fillable = [
        'work_order_number','currency_id', 'vendor_id', 'buyer_id', 'buyer_user_id', 'branch_id',
        'order_status', 'order_price_basis', 'order_payment_term',
        'order_delivery_period', 'order_remarks', 'order_add_remarks',
        'prepared_by', 'approved_by', 'created_at',
    ];
    public function products()
    {
        return $this->hasMany(WorkOrderProduct::class, 'work_order_id');
    }
    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id','user_id');
    }
    public function branch()
    {
        return $this->belongsTo(BranchDetail::class, 'branch_id', 'branch_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'user_id');
    }


    public function buyerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // public function getBranchIdAttribute()
    // {
    //     return $this->products->first()?->inventory?->branch_id;
    // }

    public function getBranchNameAttribute()
    {
        return $this->products->first()?->inventory?->branch?->name;
    }

    public function order_products()
    {
        return $this->hasMany(WorkOrderProduct::class, 'work_order_id', 'id');
    }

    public function currencyDetails()
    {
        return $this->belongsTo(Currency::class, 'currency_id','id');
    }
    
}
