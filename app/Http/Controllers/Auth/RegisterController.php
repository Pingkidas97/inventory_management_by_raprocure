<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Models\UserSession;
use App\Helpers\EmailHelper;
use DB;

class RegisterController extends Controller
{
    
    public function register(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'status' => false,
                'redirect_url' => $this->getDashboardRoute(Auth::user()->user_type),
                'message' => "Session is already running"
            ], 200);
        }

        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->merge([
            'user_type' => trim($request->user_type),
            'company_name' => trim($request->company_name),
            'name' => trim($request->name),
            'email' => trim($request->email),
            'mobile' => trim($request->mobile),
            'country_code' => trim($request->country_code),
            'password' => trim($request->password),
            'password_confirmation' => trim($request->password_confirmation),
        ]);

        $validator = Validator::make($request->all(), [
            'user_type' => ['required', Rule::in([1, 2])],
            'company_name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
            'name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z ]+)$/'],
            'email' => 'required|email|max:255|unique:users,email',
            'country_code' => [
                'required', 
                'max:5', 
                'regex:/^[0-9]+$/',
                Rule::in(DB::table("countries")->select("phonecode")->pluck("phonecode")->toArray())
            ],
            'mobile' => [
                'required',
                'regex:/^[0-9]+$/', // only digits
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->country_code === '91' && strlen($value) !== 10) {
                        $fail('Mobile number must be exactly 10 digits for country code 91.');
                    } elseif ($request->country_code !== '91' && strlen($value) > 25) {
                        $fail('Mobile number must not exceed 25 digits.');
                    }
                },
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('country_code', $request->country_code);
                }),
            ],
            'password'  => 'required|string|min:8|max:25|confirmed',
            'password_confirmation' => 'required|string|min:8|max:25',
            'captcha'  => 'required|captcha'
        ], [
            'user_type.required'    => 'Something went wrong.',
            'user_type.in'          => 'Something went wrong',
            'company_name.required' => 'Company Name is required.',
            'company_name.regex'    => 'Company Name field only support alphanumeric.',
            'name.required'         => 'Person Name is required.',
            'name.regex'            => 'Person Name field only support alphabetic.',
            'email.required'        => 'Please enter Email',
            'email.email'           => 'Please enter valid email',
            'email.unique'          => 'This email already exists.',
            'country_code.required' => 'Country code is required.',
            'country_code.in'       => 'Country code is invalid.',
            'mobile.required'       => 'Mobile number is required.',
            'mobile.regex'          => 'Mobile number must contain digits only.',
            'mobile.unique'         => 'This mobile already exists.',
            'password.required'     => 'Password is required.',
            'password.min'          => 'Password must be minimum 8 characters.',
            'password.confirmed'    => 'Password And Confirm Password Are Not Same!',
            'password_confirmation.required' => 'Confirmed Password is required.',
            'captcha.captcha' => 'Incorrect CAPTCHA'
        ]);

        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        $otp = rand(100000, 999999);

        $is_exists = TempUser::where("email", $request->email)->first();
        if(!empty($is_exists)){
            $tempUser = TempUser::find($is_exists->id);
            $tempUser->user_type = $request->user_type;
            $tempUser->company_name = $request->company_name;
            $tempUser->name = $request->name;
            $tempUser->email = $request->email;
            $tempUser->country_code = $request->country_code;
            $tempUser->mobile = $request->mobile;
            $tempUser->password = Hash::make($request->password);
            $tempUser->otp = $otp;
            $success_msg = "Your account already created. Please Enter Verification Code.";
        }else{
            $tempUser = new TempUser();
            $tempUser->user_type = $request->user_type;
            $tempUser->company_name = $request->company_name;
            $tempUser->name = $request->name;
            $tempUser->email = $request->email;
            $tempUser->country_code = $request->country_code;
            $tempUser->mobile = $request->mobile;
            $tempUser->password = Hash::make($request->password);
            $tempUser->otp = $otp;
            $success_msg = "Your account is created. Please verify Verification Code.";
        }
        if($tempUser->save()){
            if($request->user_type==1){
                $mail_data = buyerEmailTemplet('buyer-register-otp-verification');
            }else{
                $mail_data = vendorEmailTemplet('vendor-register-otp-verification');
            }
            $mail_msg = $mail_data->mail_message;
            $mail_subject = $mail_data->subject;

            $mail_msg = str_replace('$name', $request->name, $mail_msg);
            $mail_msg = str_replace('$email', $request->email, $mail_msg);
            $mail_msg = str_replace('$otp', $otp, $mail_msg);

            EmailHelper::sendMail($request->email, $mail_subject, $mail_msg);

            session()->flash('success', $success_msg);
            session(['otp_email' => $request->email]);

            return response()->json([
                'status' => true,
                'redirect_url' => route('register.verification-code'),
                'message' => "Account created"
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Failed to create account, please try again later!"
            ], 200);
        }
    }

    public function verificationCode()
    {
        if (Auth::check()) {
            return redirect()->intended($this->getDashboardRoute(Auth::user()->user_type));
        }
        
        if (!session()->has('otp_email')) {
            session()->flash('error', "Session time out.");
            return redirect()->to("/");
        }

        return view('auth.verification-code');
    }
    public function verifyVerificationCode(Request $request)
    {
        if (!session()->has('otp_email')) {
            session()->flash('error', "Session time out.");
            return response()->json([
                'status' => false,
                'redirect_url' => url('/'),
                'message' => "Session time out."
            ], 200);
        }
        
        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->merge([
            'verification_code' => trim($request->verification_code)
        ]);

        $validator = Validator::make($request->all(), [
            'verification_code' => [
                'required', 
                Rule::exists('temp_users', 'otp')->where('email', session('otp_email')),
            ]
        ], [
            'verification_code.required' => 'Verification Code is required',
            'verification_code.exists'   => 'Invalid Verification Code. Please provide current Verification Code.'
        ]);

        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        $email = session('otp_email');

        $tempUser = TempUser::where("email", $email)->first();

        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = $tempUser->name;
            $user->email = $tempUser->email;
            $user->country_code = $tempUser->country_code;
            $user->mobile = $tempUser->mobile;
            $user->password = $tempUser->password;
            $user->status = 1;
            $user->user_type = $tempUser->user_type;
            $user->save();

            $user_id = $user->id;

            if($tempUser->user_type==1){
                $buyer = new Buyer();
                $buyer->user_id = $user_id;
                $buyer->legal_name = $tempUser->company_name;
                $buyer->save();
            }else{
                $vendor = new Vendor();
                $vendor->user_id = $user_id;
                $vendor->legal_name = $tempUser->company_name;
                $vendor->save();
            }

            Auth::login($user);

            session()->regenerate();
            $sessionId = session()->getId();

            $is_session_exists = UserSession::where('user_id', $user_id)->first();
            if(!empty($is_session_exists)){
                $userSession = UserSession::find($is_session_exists->id);
                $userSession->timestamp = time();
                $userSession->data = $sessionId;
                $userSession->updated_date = date('Y-m-d H:i:s');
                $userSession->save();
            }else{
                UserSession::insert([
                    'user_id' => $user_id, 
                    'timestamp' => time(), 
                    'data' => $sessionId
                ]);
            }

            session()->forget('otp_email');
            TempUser::where("id", $tempUser->id)->delete();

            session([
                'legal_name' => $tempUser->company_name
            ]);

            session()->flash('success', "Your account successfully Registered.");
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Your account successfully Registered.',
                'redirect_url' => $this->getProfileRoute($tempUser->user_type)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            // throw $e;
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ]);
        }

    }
    private function getDashboardRoute($userType)
    {
        return match ($userType) {
            '1' => route('buyer.dashboard'),
            '2' => route('vendor.dashboard'),
            '3' => route('admin.dashboard'),
            default => route('dashboard'),
        };
    }
    private function getProfileRoute($userType)
    {
        return match ($userType) {
            '1' => route('buyer.profile'),
            '2' => route('vendor.profile'),
            default => route('dashboard'),
        };
    }
    public function resendVerificationCode(Request $request)
    {
        
        if (!session()->has('otp_email')) {
            session()->flash('error', "Session Time Out");
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'redirect_url' => url("/")
            ]);
        }
        
        $email = session('otp_email');
        $tempUser = TempUser::where("email", $email)->first();

        if(empty($tempUser)){
            session()->flash('error', "Session Time Out");
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'redirect_url' => url("/")
            ]);
        }

        $otp = rand(100000, 999999);

        $updateTempUser = TempUser::find($tempUser->id);
        $updateTempUser->otp = $otp;

        if($updateTempUser->save()){

            if($tempUser->user_type==1){
                $mail_data = buyerEmailTemplet('buyer-register-otp-verification');
            }else{
                $mail_data = vendorEmailTemplet('vendor-register-otp-verification');
            }
            $mail_msg = $mail_data->mail_message;
            $mail_subject = $mail_data->subject;

            $mail_msg = str_replace('$name', $tempUser->name, $mail_msg);
            $mail_msg = str_replace('$email', $tempUser->email, $mail_msg);
            $mail_msg = str_replace('$otp', $otp, $mail_msg);

            EmailHelper::sendMail($tempUser->email, $mail_subject, $mail_msg);

            return response()->json([
                'status' => true,
                'message' => 'Verification Code resent successfully.'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Failed to resend Verification Code'
            ]);
        }
    }
}
