<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Uom extends Model
{
    use HasFactory;

    protected $table = 'uoms';

    public $timestamps = true;
    public function inventories()
    {
        return $this->hasMany(Inventories::class);
    }
    // protected static function booted()
    // {
    //     static::addGlobalScope('ordered', function (Builder $builder) {
    //         $builder->orderBy('uom_name', 'asc');
    //     });
    // }


}
