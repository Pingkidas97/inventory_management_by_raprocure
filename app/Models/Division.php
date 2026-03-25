<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'divisions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'division_name',
        'status',
        'created_by',
        'updated_by'
    ];
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
   
}