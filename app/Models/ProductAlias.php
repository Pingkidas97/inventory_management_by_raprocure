<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAlias extends Model
{
    protected $table = 'product_alias';

    protected $fillable = [
        'product_id',
        'vendor_id',
        'alias_of',
        'is_new',
        'alias',
        'created_by',
        'updated_by',
    ];

    // Optional: relationships
    // public function product() {
    //     return $this->belongsTo(Product::class);
    // }

    // public function vendor() {
    //     return $this->belongsTo(Vendor::class);
    // }

    public static function getAliasesByProduct($productId, $vendorId = null)
    {
        $query = self::where('product_id', $productId);

        if ($vendorId !== null) {
            $query->where('vendor_id', $vendorId);
        }

        return $query->pluck('alias')->implode(', ');
    }
}

