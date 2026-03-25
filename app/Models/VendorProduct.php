<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    protected $table = 'vendor_products';

    // Add all fields you mass assign here
    protected $fillable = [
        'product_id',
        'description',
        'dealer_type_id',
        'gst_id',
        'hsn_code',
        'dealership',
        'brand',
        'country_of_origin',
        'vendor_id',
        'added_by_user_id',
        'edit_status',
        'approval_status',
        'image',
        'catalogue',
        'dealership_file'
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id'); 
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relation to Division through Product
    public function division()
    {
        return $this->product->belongsTo(Division::class, 'division_id');
    }

    // Relation to Category through Product
    public function category()
    {
        return $this->product->belongsTo(Category::class, 'category_id');
    }
  
    public function productAliases()
    {
        return $this->hasMany(\App\Models\ProductAlias::class, 'product_id', 'product_id');
    }
    public function receivedfrom()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function vendor_profile()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'vendor_id');
    }

    public function gallery()
    {
        return $this->hasMany(ProductGallery::class, 'product_id');
    }

}
