<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqProductVariant extends Model
{
     public function uoms(){
        return $this->belongsTo(Uom::class, 'uom');
    }
    //start pingki
    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'inventory_id');
    }
    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id', 'rfq_id');
    }
    public function orderVariants()
    {
        return $this->hasMany(OrderVariant::class, 'rfq_product_variant_id', 'id');
    }
    public function orderVariantsActiveOrder()
    {
        return $this->hasMany(OrderVariant::class, 'rfq_product_variant_id', 'id')->whereHas('orderActive');;
    }

    //end pingki
}
