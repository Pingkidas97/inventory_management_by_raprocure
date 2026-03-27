<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventories extends Model
{
    use HasFactory;

    protected $table = 'inventories';
    protected $fillable = [
        'inventory_unique_id',
        'item_code',
        'buyer_parent_id',
        'buyer_branch_id',
        'product_id',
        'product_name',
        'buyer_product_name',
        'specification',
        'size',
        'opening_stock',
        'stock_price',
        'uom_id',
        'inventory_grouping',
        'cost_center',
        'inventory_type_id',
        'indent_min_qty',
        'product_brand',
        'is_indent',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    // protected $with = ['buyerParent', 'product', 'uom', 'inventoryType', 'createdBy', 'updatedBy'];

    // Relationships
    public function buyerParent()
    {
        return $this->belongsTo(User::class, 'buyer_parent_id')->withDefault();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withDefault();
    }

    public function getDisplayProductNameAttribute()
    {
        return $this->product_id == 0
            ? $this->buyer_product_name
            : $this->product->name;
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id')->withDefault();
    }

    public function inventoryType()
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id')->withDefault();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault();
    }

    public function branch()
    {
        // return $this->belongsTo(BranchDetail::class, 'buyer_branch_id','branch_id');
        return $this->belongsTo(BranchDetail::class, 'buyer_branch_id', 'branch_id')
                ->where('user_type', '1');
    }
    public function indents()
    {
        return $this->hasMany(Indent::class,'inventory_id')
                    ->where('is_active', 1)
                    ->where('inv_status', 1)
                    ->where('closed_indent', 2)
                    ->where('is_deleted', 2);
    }
    public function indentsForPLC()
    {
        return $this->hasMany(Indent::class,'inventory_id')->where('is_active', 1)->where('is_deleted', 2);
    }
    
    public function manualOrderProductForPLC()
    {
        return $this->hasMany(ManualOrderProduct::class,'inventory_id');
    }
    public function RfqProductVariantForPLC()
    {
        return $this->hasMany(RfqProductVariant::class,'inventory_id');
    }
    
    public function indentRfqs()
    {
        return $this->hasMany(IndentRfq::class, 'inventory_id');
    }
}
