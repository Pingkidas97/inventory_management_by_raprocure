<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpSupport extends Model
{
    
    public function creater(){
        return $this->belongsTo(User::class,'created_by');
    }

    static function getStatus($status){
        $result=[];
        switch ($status) {
            case '1':
                $result= ['status' => 'Query Received', 'class' => 'badge-warning'];
                break;
            case '2':
                $result=['status' => 'Response Sent', 'class' => 'badge-danger'];
                break;
            case '3':
                $result=['status' => 'Closed', 'class' => 'badge-success'];
                break;
            default:
            $result=['status' => 'Query Received', 'class' => 'badge-warning'];
                break;
        }
        return $result;
    }

    public function venderBuyer()
    {
        if ($this->user_type == 1) {
            return $this->belongsTo(Buyer::class, 'company_id');
        } else {
            return $this->belongsTo(Vendor::class, 'company_id');
        }
    }

     
}
