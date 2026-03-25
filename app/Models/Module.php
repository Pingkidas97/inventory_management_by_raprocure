<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
   protected $fillable = [
        'role_name',
        'role_name_for',
        'user_master_id',
        'user_id',
        'is_active',
    ];
}
