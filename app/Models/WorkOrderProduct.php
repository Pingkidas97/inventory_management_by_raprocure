<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderProduct extends Model
{
    protected $fillable = [
        'work_order_id',
        'product_description',
        'product_price',
        'product_mrp',
        'product_disc',
        'product_total_amount',
        'product_gst',
    ];
    public $timestamps = false;

    public function WorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
    public function tax()
    {
        return $this->belongsTo(Tax::class,'product_gst');
    }
    

    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'inventory_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(WorkOrderProduct::class, 'work_order_id');
    }

    public function branch()
    {
        return $this->inventory->branch();

    }
    public function vendorProducts()
    {
        return $this->hasMany(VendorProduct::class, 'product_id', 'product_id');
    }
}
