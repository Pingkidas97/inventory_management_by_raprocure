<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersBranch extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_branch';
    public function branchDetail()
    {
        return $this->belongsTo(BranchDetail::class, 'branch_id', 'branch_id')->where('record_type', 1)->where('user_type', 1)->where('is_regd_address', 2)->where('user_id', getParentUserId());
    }
}
