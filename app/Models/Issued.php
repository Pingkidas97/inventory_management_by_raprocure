<?php

// app/Models/Issued.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issued extends Model
{
    use HasFactory;

    protected $table = 'issued';
    protected $fillable = [
        'buyer_id',
        'inventory_id',
        'issued_no',
        'qty',
        'consume_qty',
        'issued_return_for',
        'remarks',
        'issued_to',
        'issued_type',
        'inv_status',
        'consume',
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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function issuedTo()
    {
        return $this->belongsTo(Issueto::class, 'issued_to', 'id');
    }


    public function issuedType()
    {
        return $this->belongsTo(IssuedType::class, 'issued_type');
    }

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'issued_return_for','id');
    }
    
    public function getAmountAttribute()
    {
        if ($this->issued_return_for == 0) {
            return $this->qty * ($this->inventory->stock_price ?? 0);
        }

        $grn = $this->grn;

        return $this->qty * ($grn?->order_rate ?? 0);
        
    }
    public function getConsumeAmountAttribute()
    {
        if ($this->issued_return_for == 0) {
            return $this->consume_qty * ($this->inventory->stock_price ?? 0);
        }

        $grn = $this->grn;

        return $this->consume_qty * ($grn?->order_rate ?? 0);
        
    }

    //start for product wise stock ledger
    public function getReferenceNumberAttribute()
    {
        if ($this->issued_return_for == 0) {
            return 'Opening Stock';
        }

        return optional($this->grn)->po_number;
    }
    public function getRateAttribute()
    {
        if ($this->issued_return_for == 0) {
            return $this->inventory->stock_price ?? 0;
        }

        $grn = $this->grn;

        return ($grn?->order_rate ?? 0);
    }
    //end for product wise stock ledger

    public function consumes()
    {
        return $this->hasMany(Consume::class, 'issued_id');
    }
    
}
