<?php

namespace App\Traits;

trait HasModulePermission
{
    protected function ensurePermission_old(string $moduleSlug, string $permissionType = 'view', string $moduleFor = '3'): void
    {
        if (!checkPermission($moduleSlug, $permissionType, $moduleFor)) {
            abort(403, 'Unauthorized');
        }
    }

    protected function ensurePermission(string $moduleSlug, string $permissionType = 'view', string $moduleFor = '3'): void
    {
        // dd('NEW FUNCTION HIT');
        if (!checkPermission($moduleSlug, $permissionType, $moduleFor)) {
            $request = request();
            if ($request->ajax()) {
                response()->json([
                    'status' => false,
                    'message' => 'You are not authorized to access this page.'
                ], 403)->send();
                exit;
            } else {
                // get auth user id here
                $userId = auth()->check() ? auth()->id() : null;
                // You can log, use, or flash this ID as needed
                if(empty($userId)){
                    // If user is not authenticated, redirect to login
                    session()->flash('error', 'Session Time Out.');
                    redirect()->route('login')->send();
                    exit;
                }else{
                    redirect()->route('403.unauthorized')->send();
                    exit;
                }
            }
        }
    }
    private function getDashboardRoute($userType)
    {
        return match ($userType) {
            '1' => 'buyer.dashboard',
            '2' => 'vendor.dashboard',
            '3' => 'admin.dashboard',
            default => route('dashboard'),
        };
    }
    
}

