<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedReturn extends Model
{
    use HasFactory;

    protected $table = 'issued_returns';
    protected $fillable = [
        'buyer_id',
        'inventory_id',
        'branch_id',
        'issue_unique_no',
        'qty',
        'vendor_name',
        'issued_return_for',
        'remarks',
        'issued_return_type',
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

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'issued_return_for');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function issuedType()
    {
        return $this->belongsTo(IssuedType::class, 'issued_return_type');
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
}
