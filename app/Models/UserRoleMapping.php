<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleMapping extends Model
{   
    protected $table = 'user_role_mappings';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
}

}
