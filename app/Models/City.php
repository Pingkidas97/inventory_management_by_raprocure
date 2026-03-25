<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['city_name', 'state_id'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function vendor_city()
    {
        return $this->hasOne(Vendor::class, 'city', 'id');
    }
    public function buyer_city()
    {
        return $this->hasOne(Buyer::class, 'city', 'id');
    }
}
