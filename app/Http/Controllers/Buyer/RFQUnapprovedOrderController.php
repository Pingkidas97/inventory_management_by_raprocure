<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RFQUnapprovedOrderController extends Controller
{
    public function index()
    {
        return view('buyer.unapproved-orders.index');
    }
    public function create($rfq_id)
    {
        return view('buyer.unapproved-orders.create', compact('rfq_id'));
    }
}
