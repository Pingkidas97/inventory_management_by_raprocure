<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!empty(Auth::user()->parent_id)){
            $profile_user = User::find(Auth::user()->parent_id);
        }else{
            $profile_user = Auth::user();
        }
        if($profile_user->is_profile_verified==2 || $profile_user->is_verified==2){
            $redirect_url = '';
            if($profile_user->user_type==1){
                $redirect_url = route('buyer.profile');
            }else if($profile_user->user_type==2){
                $redirect_url = route('vendor.profile');
            }
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Profile not verified.',
                ], 403); // 403 Forbidden
            }else{
                return redirect()->to($redirect_url);
            }
        }
        return $next($request);
    }
}
