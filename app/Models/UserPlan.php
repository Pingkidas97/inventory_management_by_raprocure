<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{   
    protected $table = 'user_plans';

    protected $fillable = [
        'user_type',
        'user_id',
        'plan_id',
        'plan_name',
        'plan_amount',
        'trial_period',
        'no_of_users',
        'discount',
        'gst',
        'final_amount',
        'start_date',
        'subscription_period',
        'next_renewal_date',
        'is_expired',
        'activated_by',
        'created_at'
    ];
    
    public static function isActivePlan($user_id)
    {
        $plan = self::where('user_id', $user_id)
                    // ->whereIn('payment_salt', [NULL, ''])
                    ->where(function ($q) {
                        $q->whereNull('payment_salt')
                        ->orWhere('payment_salt', '');
                    })
                    ->where('is_expired', 2)
                    ->first();

        if (!empty($plan)) {
            return $plan;
        }else{
            $plan = self::where('user_id', $user_id)->first();
            if (!empty($plan)) {
                return array();
            }else{
                return array('status'=>true);
            }
        }
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
