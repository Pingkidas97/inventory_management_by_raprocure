<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedType extends Model
{
    public function issued()
    {
        return $this->hasMany(Issued::class, 'issued_type');
    }
    public function stockReturn()
    {
        return $this->hasMany(ReturnStock::class, 'stock_return_type');
    }
}
