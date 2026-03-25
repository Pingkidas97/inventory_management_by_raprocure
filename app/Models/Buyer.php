<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
class Buyer extends Model
{
    protected $fillable = [
        'user_id',
        'buyer_code',
        'legal_name',
        'incorporation_date',
        'registered_address',
        'country',
        'state',
        'city',
        'pincode',
        'gstin',
        'pan',
        'pan_file',
        'website',
        'product_details',
        'organisation_description',
        'organisation_short_code',
        'buyer_accept_tnc',
        'tab1_status',
        'tab2_status',
        'tab3_status',
        'tab4_status',
        'assigned_manager',
        'rfq_number',
        'updated_by',
    ];
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where('user_type', 1);
    }
    public function latestPlan()
    {
        return $this->hasOne(UserPlan::class,'user_id', 'user_id')->latestOfMany();
    }
    public function buyerVerifiedAt()
    {
        return $this->hasOne(UserPlan::class, 'user_id', 'user_id')->orderBy('id', 'asc');
    }
    
    public function buyerUser()
    {
        // return $this->hasMany(User::class, 'parent_id', 'user_id');
        return $this->hasMany(User::class, 'parent_id', 'user_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'buyer_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id', 'user_id');
    }

    public function getLastLoginDate($userId)
    {
        $lastLogin = DB::table('user_session')
            ->where('user_id', $userId)
            ->max('updated_date');

        if (!empty($lastLogin) && $lastLogin !== '0000-00-00 00:00:00') {
            return date('d/m/Y', strtotime($lastLogin));
        }

        return '-';
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function buyer_country()
    {
        return $this->hasOne(Country::class, 'id', 'country');
    }
    
    public function buyer_state()
    {
        return $this->hasOne(State::class, 'id','state');
    }

    public function buyer_city()
    {
        return $this->hasOne(City::class, 'id', 'city');
    }
    public function apiInfo(): HasOne
    {
        return $this->hasOne(ApiInfo::class, 'buyer_id', 'user_id')->where('is_deleted', 2);
    }
    public function branchDetails()
    {
        return $this->hasMany(BranchDetail::class, 'user_id', 'id');
    }
    
}

