<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
class HomeController extends Controller
{
    public function index(Request $request)
    {
        $email='raprocure.testing@gmail.com';
        // $request->validate([
        //     'email' => 'required|email'
        // ]);
        $otp = rand(100000, 999999); // 6-digit OTP
        echo EmailHelper::sendMail($email, 'OTP Verification', 'Your OTP is: ' . $otp);
        // Store OTP in cache for 5 minutes
        Cache::put('otp_' . $email, $otp, now()->addMinutes(5));
        //Mail::to($email)->send(new OtpMail($otp)); die;
        return response()->json(['message' => 'OTP sent successfully.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|integer'
        ]);
        $email = $request->email;
        $otp = $request->otp;
        $storedOtp = Cache::get('otp_' . $email);
        if ($storedOtp && $storedOtp == $otp) {
            Cache::forget('otp_' . $email); // Invalidate OTP after successful verification
            return response()->json(['message' => 'OTP verified successfully.']);
        }
        return response()->json(['message' => 'Invalid or expired OTP.'], 400);
    }
}