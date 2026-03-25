<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'category_id',
        'product_name',
        'status',
        'created_by',
        'updated_by',
    ];
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function product_vendors()
    {
        return $this->hasMany(VendorProduct::class, 'product_id', 'id');
    }

    public function product_aliases()
    {
        return $this->hasMany(ProductAlias::class, 'product_id', 'id');
    }
    public function master_alias()
    {
        return $this->hasMany(ProductAlias::class, 'product_id', 'id')->where('alias_of', 1)->where('is_new', 1);
    }
    public function vendor_alias()
    {
        return $this->hasMany(ProductAlias::class, 'product_id', 'id')->where('alias_of', 2)->where('is_new', 1);
    }
    public function rfq_products()
    {
        return $this->hasMany(RfqProduct::class, 'product_id');
    }
    public function order_variants()
    {
        return $this->hasMany(OrderVariant::class,'product_id','id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function inventories()
    {
        return $this->hasMany(Inventories::class);
    }
}
 
