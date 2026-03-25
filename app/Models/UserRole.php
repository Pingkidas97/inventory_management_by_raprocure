<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';
    
    protected $fillable = [
        'role_name',
        'role_name_for',
        'user_master_id',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'role_name_for' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function getRoleNameForOptions()
    {
        return [
            '1' => 'Buyer',
            '2' => 'Vendor',
            '3' => 'Super Admin'
        ];
    }

    // Add this relationship
    public function permissions()
    {
        return $this->hasMany(UserRoleModulePermission::class, 'user_role_id', 'id');
    }

    public function mappings()
    {
        return $this->hasMany(UserRoleMapping::class, 'user_role_id');
    }

}