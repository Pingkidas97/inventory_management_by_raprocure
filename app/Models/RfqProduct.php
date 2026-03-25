<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqProduct extends Model
{   
    protected $table = 'rfq_products';
    protected $guarded = [];
    public $timestamps = true;
    
    public function masterProduct()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function productVariants()
    {
        return $this->hasMany(RfqProductVariant::class, 'product_id', 'product_id');
    }
    public function productVendors()
    {
        return $this->hasMany(VendorProduct::class, 'product_id', 'product_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id','rfq_id');
    }
}
