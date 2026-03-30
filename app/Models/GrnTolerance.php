<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnTolerance extends Model
{
    protected $table = 'grn_tolerance';

    protected $fillable = [
        'buyer_id',
        'tolerance',
        'created_by',
        'updated_by'
    ];
}