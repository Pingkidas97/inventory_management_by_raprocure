<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\UserPlan;
use App\Models\UserSession;
use App\Helpers\CustomHelper;
use DB;
// use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // For debugging
use Illuminate\Support\Facades\Validator;
class LoginController extends Controller
{
    public function __construct()
    {
        // parent::__construct(); // Call parent constructor (optional)
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended($this->getDashboardRoute(Auth::user()->user_type));
        }
        $country_code = DB::table("countries")
                                ->select("name", "phonecode")
                                ->orderBy("name", "ASC")
                                ->pluck("name", "phonecode")->toArray();

        return view('auth.login', compact('country_code'));
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended($this->getDashboardRoute(Auth::user()->user_type));
        }

        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ]);
        
        $decryptedpassword = User::decryptPassword(env("AUTH_ENCRYPTION_KEY", "C7zjDVG0fnjVVwjd"), $request->password);
                
        $user = User::where('email', $request->email)->whereIn('user_type', ['1', '2'])->first();

        if (!$user) {
            LoginAttempt::incrementFailedAttempts($request->email, $request->ip());

            return response()->json([
                'status' => false,
                'message' => 'Your details are not valid'
            ]);
        }

        if ($user->status !== '1') {
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive'
            ]);
        }

        $login_info = LoginAttempt::checkLoginAttempts($request->email, $request->ip());
        if ($login_info) {
            if ($login_info->lockout_time && strtotime($login_info->lockout_time) > time()) {
                $lockout_time_remaining = strtotime($login_info->lockout_time) - time();
                $msg= "Account is locked. Try again in ".round($lockout_time_remaining/60)." minutes.";
                return response()->json([
                    'status' => false,
                    'message' => $msg
                ]);
            }
        }
        if (User::attemptLogin($user, $decryptedpassword)) {
            Auth::login($user, $request->filled('remember'));

            // reset failed login attempts
            LoginAttempt::resetFailedAttempts($request->email, $request->ip());
            
            $buyer_parent_id = !empty($user->parent_id) ? $user->parent_id : $user->id;

            $is_plan_active = UserPlan::isActivePlan($buyer_parent_id);
            
            if (empty($is_plan_active)) {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => "Your subscription plan has Expired!"
                ]);
            }

            if($buyer_parent_id!=$user->id){
                $parent_buyer = User::find($buyer_parent_id);
                if (!empty($parent_buyer) && ($parent_buyer->status != 1 || $parent_buyer->is_verified != 1)) {
                    Auth::logout();
                    return response()->json([
                        'status' => false,
                        'message' => "Your account is inactive"
                    ]);
                }
            }

            session()->regenerate();
            $sessionId = session()->getId();
            $is_session_exists = UserSession::where('user_id', $user->id)->first();
            if(!empty($is_session_exists)){
                $userSession = UserSession::find($is_session_exists->id);
                $userSession->timestamp = time();
                $userSession->data = $sessionId;
                $userSession->updated_date = date('Y-m-d H:i:s');
                $userSession->save();
            }else{
                UserSession::insert([
                    'user_id' => $user->id,
                    'timestamp' => time(), 
                    'data' => $sessionId
                ]);
            }
            
            if(Auth::user()->user_type==1){
                $company_tbl = 'buyers';
            }else{
                $company_tbl = 'vendors';
            }
            $company_data = DB::table($company_tbl)->select("legal_name")->where('user_id', getParentUserId())->first();
            $legal_name = $company_data->legal_name;
            // if(Auth::user()->user_type==1){
            //     $company_data = DB::table("buyers")->select("legal_name")->where('user_id', Auth::user()->id)->first();
            //     $legal_name = $company_data->legal_name;
            // }else{
            //     $company_data = DB::table("vendors")->select("legal_name")->where('user_id', Auth::user()->id)->first();
            //     $legal_name = $company_data->legal_name;
            // }
            session([
                'legal_name' => $legal_name
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'redirect_url' => $this->getDashboardRoute($user->user_type)
            ]);
        }

        LoginAttempt::incrementFailedAttempts($request->email, $request->ip());

        return response()->json([
            'status' => false,
            'message' => 'Your details are not valid'
        ]);
    }

    private function getDashboardRoute($userType)
    {
        if(!empty(Auth::user()->parent_id)){
            $profile_user = User::find(Auth::user()->parent_id);
        }else{
            $profile_user = Auth::user();
        }
        if($profile_user->is_profile_verified==1){
            return match ($userType) {
                '1' => route('buyer.dashboard'),
                '2' => route('vendor.dashboard'),
                '3' => route('admin.dashboard'),
                default => route('dashboard'),
            };
        }else{
            return match ($userType) {
                '1' => route('buyer.profile'),
                '2' => route('vendor.profile'),
                '3' => route('admin.dashboard'),
                default => route('dashboard'),
            };
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('legal_name');
        return redirect('/login');
    }

    public function forgotPassword(Request $request)
    {
        return view('auth.forgot-password');
    }

    public function forgotPasswordSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() // returns only the first error
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }
        $token = Str::random(64);
        $user->password_reset_token = $token;
        $user->password_reset_expires_at = now()->addMinutes(30);
        $user->save();

        $to = $user->email;
        $subject = "Password Reset Request";
        $body = "You have requested to reset your password. Please click the link below to reset your password.";
        $body .= "<br><a href='" . route('reset-password', ['token' => $token]) . "'>Reset Password</a>";
        $body .= "<br><br>Thank you.";
        sendMail($to, $subject, $body, $mailer='smtp');
        return response()->json([
            'success' => true,
            'message' => 'Password reset email sent successfully'
        ]);
    }
    public function resetPassword(Request $request)
    {
        $token = $request->token;
        $user = User::where('password_reset_token', $token)->first();
        if (!$user) {
            return view('auth.reset-password', compact('token'));
        }
        return view('auth.reset-password', compact('token'));
    }   

    public function resetPasswordSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() // returns only the first error
            ]);
        }
        $token = $request->token;
        $user = User::where('password_reset_token', $token)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 404);
        }
        if($user->password_reset_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 404);
        }
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}
