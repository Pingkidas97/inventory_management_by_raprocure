<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForceClosure extends Model
{
    protected $table = 'force_closures';

    protected $fillable = [
        'inventory_id',
        'rfq_id',
        'rfq_number',
        'rfq_product_variant_id',
        'original_rfq_quantity',
        'updated_rfq_quantity',
        'total_order_quantity',
        'total_grn_quantity',
        'buyer_parent_id',
        'buyer_id'
    ];
}