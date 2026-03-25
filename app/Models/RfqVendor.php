<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendor extends Model
{   

    protected $table = 'rfq_vendors';
    
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function rfqVendorProfile()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'vendor_user_id');
    }
    
    public function rfqVendorDetails()
    {
        return $this->hasOne(User::class, 'id', 'vendor_user_id');
    }
}
