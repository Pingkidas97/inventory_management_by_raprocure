<?php

// app/Models/InventoryType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InventoryType extends Model
{
    use HasFactory;

    protected $table = 'inventory_type';
    protected $fillable = [
        'name',
        'status',
    ];
    public $timestamps = true;   
}

