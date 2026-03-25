<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    public function rfqBuyer()
    {
        return $this->belongsTo(User::class, 'id', 'buyer_id');
    }
    public function rfqProducts()
    {
        return $this->hasMany(RfqProduct::class, 'rfq_id', 'rfq_id');
    }
    public function rfqVendors()
    {
        return $this->hasMany(RfqVendor::class, 'rfq_id', 'rfq_id');
    }
    public function rfqVendorQuotations()
    {
        return $this->hasMany(RfqVendorQuotation::class, 'rfq_id', 'rfq_id');
    }
    public function buyerUser() {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }
    public function buyerBranch() {
        return $this->belongsTo(BranchDetail::class, 'buyer_branch', 'branch_id');
    }
    public function buyer() {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'user_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vend_id');
    }
    public function products()
    {
        return $this->hasMany(RfqProduct::class, 'rfq_id', 'rfq_id');
    }

    public function buyer_branchs()
    {
        return $this->belongsTo(BranchDetail::class, 'buyer_branch','branch_id');
    }

    public function rfq_generated_by()
    {
        return $this->belongsTo(User::class, 'buyer_user_id', 'id');
    }
    public function rfq_auction()
    {
        return $this->hasOne(RfqAuction::class, 'rfq_no', 'rfq_id');
    }

    public static function rfqDetails($rfq_id) {
        $cis = self::where('rfq_id', $rfq_id)
                    ->select('id', 'rfq_id', 'buyer_id', 'buyer_user_id', 'prn_no', 'buyer_branch', 'last_response_date', 'buyer_price_basis', 'buyer_pay_term', 'buyer_delivery_period', 'scheduled_date',
                        'buyer_rfq_status', 'inventory_id', 'created_at', 'updated_at',)
                    ->with([
                        'rfqVendorQuotations' => function ($q) {
                            $q->select('id', 'rfq_id', 'vendor_id', 'rfq_product_variant_id', 'price', 'mrp', 'discount', 'buyer_price',
                                'vendor_brand', 'vendor_remarks', 'vendor_additional_remarks', 'vendor_price_basis', 'vendor_payment_terms',
                                'vendor_delivery_period', 'vendor_currency', 'created_at', 'updated_at')
                            ->where('status', 1);
                        },-
                        'rfqVendors'=> function ($query) {
                            $query->select('id', 'rfq_id', 'vendor_user_id');
                        },
                        'rfqVendors.rfqVendorProfile'=> function ($query) {
                            $query->select('id', 'user_id', 'legal_name');
                        },
                        'rfqVendors.rfqVendorDetails'=> function ($query) {
                            $query->select('id', 'name', 'country_code', 'mobile');
                        },
                        'rfqProducts'=> function ($query) {
                            $query->orderBy('product_order', 'asc');
                        },
                        'rfqProducts.productVariants'=> function ($query) use($rfq_id) {
                            $query->where('rfq_id', $rfq_id)
                                ->orderBy('variant_order', 'asc');
                        },
                        'rfqProducts.masterProduct'=> function ($query) {
                            $query->select('id', 'product_name');
                        },
                        'rfq_auction'=> function ($query) {
                            $query->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time');
                        }
                    ])
                    ->first();

        $cis_array = self::analyzeRFQDetails($cis);
        return self::sortRFQDetails($cis_array);
    }

    public static function analyzeRFQDetails($cis) {


        return $cis;
    }
    public static function sortRFQDetails($cis) {
        return $cis;
    }


    //start pingki
    public function rfqProductVariants()
    {
        return $this->hasMany(RfqProductVariant::class, 'rfq_id', 'rfq_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'rfq_id', 'rfq_id');
    }

    public function orderVariants()
    {
        return $this->hasMany(OrderVariant::class, 'rfq_id', 'rfq_id');
    }
    //end pingki

}
