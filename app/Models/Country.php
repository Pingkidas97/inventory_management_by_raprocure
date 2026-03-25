<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['shortname', 'name', 'country_flag', 'phonecode', 'status'];

    public function states()
    {
        return $this->hasMany(State::class);
    }
    public function vendor_country()
    {
        return $this->hasOne(Vendor::class, 'country', 'id');
    }
    public function buyer_country()
    {
        return $this->hasOne(Buyer::class, 'country', 'id');
    }

}
