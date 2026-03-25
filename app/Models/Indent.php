<?php

// app/Models/IndentMgt.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indent extends Model
{
    use HasFactory;

    protected $table = 'indent';

    protected $fillable = [
        'buyer_id',
        'inventory_id',
        'inv_status',
        'is_active',
        'inventory_unique_id',
        'indent_qty',
        'grn_qty',
        'remarks',
        'closed_indent',
        'is_deleted',
        'created_by',
        'updated_by',        
        'approved_by_1',
        'approved_by_2'
    ];

    public $timestamps = true;

    // Relationships
    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'inventory_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function approvedBy1()
    {
        return $this->belongsTo(User::class, 'approved_by_1');
    }
    public function approvedBy2()
    {
        return $this->belongsTo(User::class, 'approved_by_2');
    }        
    public function indentRfqs()
    {
        return $this->hasMany(IndentRfq::class, 'indent_id', 'id')
                    ->whereColumn('inventory_id', 'indent.inventory_id');
    }


}
