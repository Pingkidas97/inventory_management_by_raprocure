<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;

class ValidateAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $sessionId = session()->getId();
        $is_session = UserSession::where('user_id', $user->id)
                                ->where('data', $sessionId)
                                ->first();
        
        if(empty($is_session)){
            Auth::logout();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Session Time Out.',
                ], 403); // 403 Forbidden
            }else{
                session()->flash('error', "Session Time Out");
                return redirect()->route('login');
            }
        }
        return $next($request);
    }
}
