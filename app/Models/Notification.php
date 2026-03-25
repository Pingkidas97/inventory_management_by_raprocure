<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $table = 'notifications';
    function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    function senders()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
