<?php 
namespace App\Http\Controllers\Buyer;

use App\Traits\HasModulePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class InventoryPermissionCheckController extends Controller
{
    use HasModulePermission;

    public function check(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission(
                    $request->input('module'),
                    $request->input('type', 'view'),
                    $request->input('for', '1')
                );
            }

            return response()->json([
                'status' => 1,
                'message' => 'Authorized'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function checkPermissionForInventorySectionController($module,$type,$for)
    {
        return checkPermission($module,$type,$for);
        
        // try {
        //     if (Auth::user()->parent_id != 0) {
        //         $this->ensurePermission($module,$type,$for);
        //     }
        //     return true;
        // } catch (\Exception $e) {
        //     return false;
        // }
    }

}
