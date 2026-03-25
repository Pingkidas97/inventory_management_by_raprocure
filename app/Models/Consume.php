<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    protected $table = 'consumes';

    protected $fillable = [
        'issued_id',
        'qty',
        'consume_no',
    ];

    //  Relation with Issued
    public function issued()
    {
        return $this->belongsTo(Issued::class, 'issued_id');
    }
}
