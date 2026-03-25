<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVariant extends Model
{
    protected $table = 'order_variants';
    public function order()
    {
        return $this->belongsTo(Order::class,'po_number','po_number');
    }
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function frq_variant()
    {
        return $this->belongsTo(RfqProductVariant::class,'rfq_product_variant_id','id');
    }
    public function frq_quotation_variant()
    {
        return $this->belongsTo(RfqVendorQuotation::class,'rfq_quotation_variant_id','id');
    }
    public function orderActive()
    {
        return $this->belongsTo(Order::class, 'po_number', 'po_number')
                    ->where('order_status', '1');
    }

    

}
