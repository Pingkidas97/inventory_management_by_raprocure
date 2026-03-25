<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndentRfq extends Model
{
    use HasFactory;

    protected $table = 'indent_rfq'; 
    protected $fillable = [
        'inventory_id',
        'indent_id',
        'rfq_id', 
        'used_indent_qty',     
    ];

    public $timestamps = false;

    public function inventory()
    {
        // return $this->belongsTo(Inventory::class, 'inventory_id');
        return $this->belongsTo(Inventories::class, 'inventory_id');
    }

    public function indent()
    {
        return $this->belongsTo(Indent::class, 'indent_id');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id','rfq_id');
    }
}
