<?php

// app/Models/StockReturnType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReturnType extends Model
{
    use HasFactory;

    protected $table = 'stock_return_type';

    protected $fillable = [
        'name',
        'status',
    ];

    public $timestamps = true;
}
