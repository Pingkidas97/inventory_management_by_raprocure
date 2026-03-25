<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'vendor_code', 'legal_name', 'profile_img',
        'date_of_incorporation', 'nature_of_organization', 'nature_of_business',
        'registered_address', 'pincode',
        'gstin', 'gstin_document', 'company_name1', 'company_name2',
        'registered_product_name', 'website', 'msme', 'msme_certificate',
        'iso_registration', 'iso_regi_certificate', 'description', 't_n_c',
        'referred_by', 'assigned_manager', 'updated_by'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function latestPlan()
    {
        return $this->hasOne(UserPlan::class,'user_id', 'user_id')->latestOfMany();
    }
    public function vendorVerifiedAt()
    {
        return $this->hasOne(UserPlan::class, 'user_id', 'user_id')->orderBy('id', 'asc');
    }

    public function vendor_products()
    {
        return $this->hasMany(VendorProduct::class, 'vendor_id','user_id');
    }

    public function vendorUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'parent_id');
    }

    public function vendor_country()
    {
        return $this->hasOne(Country::class, 'id', 'country');
    }
    
    public function vendor_state()
    {
        return $this->hasOne(State::class, 'id','state');
    }

    public function vendor_city()
    {
        return $this->hasOne(City::class, 'id', 'city');
    }

    public function manager() {
        return $this->belongsTo(User::class, 'assigned_manager');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }  

}
