<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'division_id',
        'category_name',
        'status',
        'created_by',
        'updated_by',
    ];
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id'); 
    }

}
