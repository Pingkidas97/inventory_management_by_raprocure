<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';
    
    protected $fillable = [
        'plan_name',
        'type',
        'no_of_user',
        'price',
        'trial_period',
        'status'
    ];  
    public const TYPE_BUYER = 1;
    public const TYPE_VENDOR = 2;

    public static function getType(): array
    {
        return [
            self::TYPE_BUYER => 'Buyer',
            self::TYPE_VENDOR => 'Vendor',
        ];
    }
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public static function getStatus(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}