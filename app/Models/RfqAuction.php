<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RfqAuction extends Model
{
    protected $appends = ['status']; // This will include 'status' in the JSON response

    public function getStatusAttribute()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        $auction_date = $this->auction_date;
        $start_time = $this->auction_start_time;
        $end_time = $this->auction_end_time;

        if ($auction_date == $today) {
            if ($now >= $start_time && $now <= $end_time) {
                return 1; // Active
            } elseif ($now < $start_time) {
                return 2; // Scheduled
            } else {
                return 3; // Closed
            }
        } elseif ($auction_date < $today) {
            return 3; // Closed
        } else {
            return 2; // Scheduled
        }
    }
    public function rfq()
    {
        return $this->belongsTo(Rfq::class,'rfq_no','rfq_id');
    }
    public function rfq_auction_variant()
    {
        return $this->belongsTo(RfqAuctionVariant::class,'id','auction_id');
    }
    public function rfq_vendor_auction()
    {
        return $this->belongsTo(RfqVendorAuction::class,'id','auction_id');
    }
    public function buyer()
    {
        return $this->belongsTo(Buyer::class,'buyer_id','user_id');
    }
}
