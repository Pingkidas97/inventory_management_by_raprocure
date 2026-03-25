<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $table = 'advertisements';
    
    protected $fillable = [
        'types',
        'buyer_vendor_name',
        'received_on',
        'payment_received_on',
        'validity_period_from',
        'validity_period_to',
        'images',
        'ads_url',
        'ad_position',
        'status'
    ];  

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