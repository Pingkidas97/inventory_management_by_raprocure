<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


class BranchDetail extends Model
{
    use HasFactory;

    protected $table = 'branch_details';
    protected $fillable = [
        'branch_id',
        'user_type',
        'record_type',
        'status',
        'is_regd_address',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getDistinctActiveBranchesByUser($userId)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();
        $branch_query = DB::table('branch_details')
                        ->select('id', 'branch_id', 'name')
                        ->where("user_id", $userId)
                        ->where('user_type', 1)
                        ->where('record_type', 1)
                        ->where('status', 1);
        if (!empty($user_branch_id_only)) {
            $branch_query->whereIn('branch_id', $user_branch_id_only);
        }
        return $branch_query->orderBy('name', 'ASC')->get();
    }
     public function branch_country()
    {
        return $this->hasOne(Country::class, 'id', 'country');
    }
    
    public function branch_state()
    {
        return $this->hasOne(State::class, 'id','state');
    }

    public function branch_city()
    {
        return $this->hasOne(City::class, 'id', 'city');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }
}
