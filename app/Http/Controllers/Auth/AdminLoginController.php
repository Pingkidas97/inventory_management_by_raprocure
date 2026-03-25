<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
 
class AdminLoginController extends Controller
{
    public function showLoginForm()
    {   
        if (Auth::check()) {
          return redirect()->route('admin.dashboard');
        }
        return view('auth.admin_login');
    }

    public function login(Request $request)
    {
        $r_password = User::decryptPassword(env("AUTH_ENCRYPTION_KEY", "C7zjDVG0fnjVVwjd"), $request->password);
        // Replace the original password with decrypted one
        $request->merge(['password' => $r_password]);
        // Sanitize input
        $sanitized = [
            'email'    => filter_var(trim($request->input('email')), FILTER_SANITIZE_EMAIL),
            'password' => trim($request->input('password')),
            'captcha'  => trim($request->input('captcha')),
        ];
        $request->merge($sanitized);
        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|max:255',
            'captcha'  => 'required|captcha'
        ], [
            'captcha.captcha' => 'The CAPTCHA verification failed. Please try again.'
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendErrorResponse($request, 'Invalid credentials', 'email', 401);
        }
        if ($user->status !== '1') {
            return $this->sendErrorResponse($request, 'Your account is inactive', 'email', 403);
        }
        if ($user->user_type !== '3') {
            return $this->sendErrorResponse($request, 'Admin access required', 'email', 403);
        }
        if (!$this->attemptLogin($user, $request->password)) {
            return $this->sendErrorResponse($request, 'Invalid credentials', 'password', 401);
        }
        Auth::login($user, $request->filled('remember'));
        // Always return JSON response for AJAX handling
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect_url' => route('admin.dashboard')
        ]);
    }

    protected function sendErrorResponse(Request $request, $message, $field = null, $statusCode = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $field ? [$field => [$message]] : []
        ], $statusCode);
    }

    protected function attemptLogin(User $user, string $password): bool
    {
        if (strlen($user->password) === 40 && ctype_xdigit($user->password)) {
            if (sha1($password) === $user->password) {
                $user->password = Hash::make($password);
                $user->save();
                $user->refresh();
                return true;
            }
            return false;
        }
        return Hash::check($password, $user->password);
    }

    function customDecrypt($encryptedPassword) {
        $decrypted = "";
        // Split the hex string into pairs of two characters
        for ($i = 0; $i < strlen($encryptedPassword); $i += 2) {
            $hexPair = substr($encryptedPassword, $i, 2);
            $transformed = hexdec($hexPair);
            // Reverse the transformation
            $position = $i / 2;
            $originalCode = ($transformed - $position) ^ 255;
            $decrypted .= chr($originalCode);
        }
        return $decrypted;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/super-admin');
    }


    public function forgotPassword(Request $request)
    {   
        $number_first=rand(1,9);
        $number_second=rand(1,9);
        $total_number=$number_first+$number_second;
        session()->put('forgot_email', $total_number);
        return view('auth.admin-forgot-password',compact('number_first','number_second'));
    }

    public function forgotPasswordSubmit(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'total_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() // returns only the first error
            ]);
        }
        $total_number=$request->total_number;
        $session_number = $request->session()->get('forgot_email');
        if($total_number!=$session_number){
            return response()->json([
                'success' => false,
                'message' => 'Invalid Verification Code. Please provide current Verification Code.'
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
        $user->password_reset_expires_at =now()->addMinutes(30);
        $user->save();
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $token
        ];
        $to = $user->email;
        $subject = "Password Reset Request";
        $body = "You have requested to reset your password. Please click the link below to reset your password.";
        $body .= "<br><a href='" . route('admin.reset-password', ['token' => $token]) . "'>Reset Password</a>";
        $body .= "<br><br>Thank you.";
        sendMail($to, $subject, $body, $mailer='smtp');
        return response()->json([
            'success' => true,
            'message' => 'Password reset email sent successfully'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $token = $request->token;
        $user = User::where('password_reset_token', $token)->first();
        if (!$user) {
            return view('auth.admin-reset-password', compact('token'));
        }
        return view('auth.admin-reset-password', compact('token'));
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
        ], 200);
    }   
}
