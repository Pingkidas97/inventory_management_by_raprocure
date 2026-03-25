<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'country_id', 'state_code'];

    public function state_country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }
    public function vendor_state()
    {
        return $this->hasOne(Vendor::class, 'state', 'id');
    }
    public function buyer_state()
    {
        return $this->hasOne(Buyer::class, 'state', 'id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
