<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerDashboardController extends Controller
{

    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        // echo session()->getId();die;
        return view('buyer.buyer-dashboard', compact('user'));
    }
    public function change_password()
    {
        $user = Auth::user();
        return view('buyer.setting.change-password', compact('user'));
    }
}
