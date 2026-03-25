<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        return view('vendor.vendor-dashboard', compact('user'));
    }
}
