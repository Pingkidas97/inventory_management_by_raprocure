<?php

// app/Models/ReturnStock.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnStock extends Model
{
    use HasFactory;

    protected $table = 'return_stocks';
    protected $fillable = [
        'buyer_id',
        'inventory_id',
        'branch_id',
        'stock_no',
        'qty',
        'vendor_name',
        'remarks',
        'stock_return_for',
        'stock_vendor_name',
        'stock_vehicle_no_lr_no',
        'stock_debit_note_no',
        'stock_frieght',
        'stock_return_type',
        'is_deleted',
        'updated_by',
    ];

    public $timestamps = false;

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'inventory_id');
    }

    public function returnType()
    {
        return $this->belongsTo(IssuedType::class, 'stock_return_type');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function grn()
    {
        return $this->belongsTo(Grn::class, 'stock_return_for');
    }
    //start for product wise stock ledger
    public function getReferenceNumberAttribute()
    {
        if ($this->stock_return_for == 0) {
            return 'Opening Stock';
        }

        return optional($this->grn)->po_number;
    }
    public function getRateAttribute()
    {
        if ($this->stock_return_for == 0) {
            return $this->inventory->stock_price ?? 0;
        }

        $grn = $this->grn;

        return ($grn?->order_rate ?? 0);
    }
    //end for product wise stock ledger
}

