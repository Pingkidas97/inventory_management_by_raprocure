<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    public function order_variants()
    {
        return $this->hasMany(OrderVariant::class, 'po_number', 'po_number');
    }
    
    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'user_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'user_id');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id', 'rfq_id');
    }

    public function order_confirmed_by()
    {
        return $this->belongsTo(User::class, 'buyer_user_id', 'id');
    }
    public function po_generated_by()
    {
        return $this->belongsTo(User::class, 'unapprove_by_user_id', 'id');
    }

}
