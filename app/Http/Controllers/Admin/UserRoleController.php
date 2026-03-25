<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserRole;
use App\Models\Module;
use App\Models\UserRoleModulePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class UserRoleController extends Controller
{   

    /**
     * Constructor to check user authorization.
     */
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Display a listing of user roles for role_name_for = 3.
     *
     * @return \Illuminate\View\View Returns a view with paginated user roles
     */
    public function index()
    {
        $roles = UserRole::where('role_name_for', 3)->paginate(25);
        return view('admin.user-roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new user role.
     *
     * @return \Illuminate\View\View Returns the user role creation form with modules and their permissions
     */
    public function create()
    {
        $modules = Module::where('module_for', 3)
            ->where('is_active', 1)
            ->orderBy('is_order')
            ->get()
            ->map(function($module) {
                // Define restricted slugs for different permission types
                $restrictedSlugsForAdd = ['ALL_VERIFIED_PRODUCTS','PRODUCTS_FOR_APPROVAL','NEW_PRODUCT_REQUEST','EDIT_PRODUCT','BUYER_MODULE','VENDOR_MODULE','BUYERS_ACCOUNTS','','VENDORS_ACCOUNTS','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','HELP_AND_SUPPORT','CHANGE_PASSWORD','BUYER_QUERY'];

                $restrictedSlugsForEdit = ['ALL_VERIFIED_PRODUCTS','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','BUYER_QUERY'];

                $restrictedSlugsForDelete = ['PRODUCT_DIRECTORY','ALL_VERIFIED_PRODUCTS','PRODUCTS_FOR_APPROVAL','NEW_PRODUCT_REQUEST','EDIT_PRODUCT','BUYER_MODULE','VENDOR_MODULE','ADVERTISEMENT_AND_MARKETING','BUYERS_ACCOUNTS','','VENDORS_ACCOUNTS','PLAN_MODULE','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','ADMIN_USERS','MANAGE_ROLE','HELP_AND_SUPPORT','CHANGE_PASSWORD','BUYER_QUERY'];

                $restrictedSlugsForView = ['CHANGE_PASSWORD'];

                // Set available permissions based on slug restrictions
                $module->available_permissions = [
                    'add' => !in_array($module->module_slug, $restrictedSlugsForAdd),
                    'edit' => !in_array($module->module_slug, $restrictedSlugsForEdit),
                    'delete' => !in_array($module->module_slug, $restrictedSlugsForDelete),
                    'view' => !in_array($module->module_slug, $restrictedSlugsForView)
                ];
                return $module;
            });

        return view('admin.user-roles.create', compact('modules'));
    }

    /**
     * Store a newly created user role in storage.
     *
     * @param Request $request The HTTP request containing role data and permissions
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->all()
            ], 422);
        }

        // Sanitize inputs
        $role_name = strip_tags(trim($request->input('role_name')));
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Check for duplicate
        $exists = UserRole::where('role_name', $role_name)
            ->where('role_name_for', 3)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 0,
                'message' => 'Role name already exists for Super Admin.'
            ], 409);
        }

        DB::beginTransaction();

        try {
            // Create User Role
            $userRole = new UserRole();
            $userRole->role_name = $role_name;
            $userRole->role_name_for = 3;
            $userRole->user_master_id = !empty($user->parent_id) ? $user->parent_id : $user->id;
            $userRole->user_id = $user->id;
            $userRole->is_active = 1;
            $userRole->save();

            // Save Permissions
            $permissions = $request->input('permissions', []);

            foreach ($permissions as $moduleId => $perm) {
                DB::table('user_role_module_permissions')->insert([
                    'user_role_id' => $userRole->id,
                    'module_id' => $moduleId,
                    'can_add' => !empty($perm['add']) ? 1 : 0,
                    'can_edit' => !empty($perm['edit']) ? 1 : 0,
                    'can_delete' => !empty($perm['delete']) ? 1 : 0,
                    'can_view' => !empty($perm['view']) ? 1 : 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'User Role created successfully!',
                'data' => $userRole
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to create user role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified user role.
     *
     * @param int $id The ID of the user role to edit
     * @return \Illuminate\View\View Returns the user role edit form with role data and permissions
     */
    public function edit($id)
    {
        $userRole = UserRole::with('permissions')->findOrFail($id);
        
        // Get all active modules for role management (module_for = 3)
        $modules = Module::where('module_for', 3)
            ->where('is_active', 1)
            ->orderBy('is_order')
            ->get()
            ->map(function($module) use ($userRole) {
                // Define restricted slugs for different permission types
                $restrictedSlugsForAdd = ['ALL_VERIFIED_PRODUCTS','PRODUCTS_FOR_APPROVAL','NEW_PRODUCT_REQUEST','EDIT_PRODUCT','BUYER_MODULE','VENDOR_MODULE','BUYERS_ACCOUNTS','','VENDORS_ACCOUNTS','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','HELP_AND_SUPPORT','CHANGE_PASSWORD','BUYER_QUERY'];

                $restrictedSlugsForEdit = ['ALL_VERIFIED_PRODUCTS','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','BUYER_QUERY'];

                $restrictedSlugsForDelete = ['PRODUCT_DIRECTORY','ALL_VERIFIED_PRODUCTS','PRODUCTS_FOR_APPROVAL','NEW_PRODUCT_REQUEST','EDIT_PRODUCT','BUYER_MODULE','VENDOR_MODULE','ADVERTISEMENT_AND_MARKETING','BUYERS_ACCOUNTS','','VENDORS_ACCOUNTS','PLAN_MODULE','DIVISION_AND_CATEGORY_WISE','BUYER_TICKET_RAISED SUMMARY','VENDOR_TICKET_RAISED_SUMMARY','VENDOR_ACTIVITY_REPORTS','BUYER_ACTIVITY_REPORTS','BUYER_REPORTS','VENDOR_REPORTS','ADMIN_USERS','MANAGE_ROLE','HELP_AND_SUPPORT','CHANGE_PASSWORD','BUYER_QUERY'];

                $restrictedSlugsForView = ['CHANGE_PASSWORD'];

                // Get the role's existing permissions for this module
                $rolePermissions = $userRole->permissions->where('module_id', $module->id)->first();

                return (object)[
                    'id' => $module->id,
                    'module_name' => $module->module_name,
                    'module_slug' => $module->module_slug,
                    'available_permissions' => [
                        'add' => !in_array($module->module_slug, $restrictedSlugsForAdd),
                        'edit' => !in_array($module->module_slug, $restrictedSlugsForEdit),
                        'delete' => !in_array($module->module_slug, $restrictedSlugsForDelete),
                        'view' => !in_array($module->module_slug, $restrictedSlugsForView)
                    ],
                    'permissions' => $rolePermissions ?: null
                ];
            });
        

        return view('admin.user-roles.edit', [
            'userRole' => $userRole,
            'modules' => $modules
        ]);
    }

    /**
     * Update the specified user role in storage.
     *
     * @param Request $request The HTTP request containing updated role data and permissions
     * @param int $id The ID of the user role to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->all()
            ], 422);
        }

        // Sanitize inputs
        $role_name = strip_tags(trim($request->input('role_name')));
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Check for duplicate (excluding current role)
        $exists = UserRole::where('role_name', $role_name)
            ->where('role_name_for', 3)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 0,
                'message' => 'Role name already exists for Super Admin.'
            ], 409);
        }

        DB::beginTransaction();

        try {

            // Update User Role
            $userRole = UserRole::findOrFail($id);
            $userRole->role_name = $role_name;
            $userRole->updated_at = now();
            $userRole->save();

            // Permissions array
            // $permissions = $request->input('permissions', []);

            // foreach ($permissions as $moduleId => $perm) {
            //     DB::table('user_role_module_permissions')->updateOrInsert(
            //         [
            //             'user_role_id' => $userRole->id,
            //             'module_id' => $moduleId
            //         ],
            //         [
            //             'can_add' => !empty($perm['can_add']) ? 1 : 0,
            //             'can_edit' => !empty($perm['can_edit']) ? 1 : 0,
            //             'can_delete' => !empty($perm['can_delete']) ? 1 : 0,
            //             'can_view' => !empty($perm['can_view']) ? 1 : 0,
            //             'is_active' => 1,
            //             'updated_at' => now()
            //         ]
            //     );
            // }

            $submittedPermissions = $request->input('permissions', []);
            $allModuleIds = DB::table('modules')->pluck('id');

            foreach ($allModuleIds as $moduleId) {
                $perm = $submittedPermissions[$moduleId] ?? [];

                DB::table('user_role_module_permissions')->updateOrInsert(
                    [
                        'user_role_id' => $userRole->id,
                        'module_id' => $moduleId
                    ],
                    [
                        'can_add' => !empty($perm['can_add']) ? 1 : 0,
                        'can_edit' => !empty($perm['can_edit']) ? 1 : 0,
                        'can_delete' => !empty($perm['can_delete']) ? 1 : 0,
                        'can_view' => !empty($perm['can_view']) ? 1 : 0,
                        'is_active' => 1,
                        'updated_at' => now()
                    ]
                );
            }


            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'User Role updated successfully!',
                'data' => $userRole
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to update user role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user role from storage.
     *
     * @param int $id The ID of the user role to delete
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Delete related permissions using Eloquent model
            UserRoleModulePermission::where('user_role_id', $id)->delete();

            // Then delete the user role
            UserRole::findOrFail($id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User Role and its permissions deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete User Role. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of the specified user role.
     *
     * @param Request $request The HTTP request containing the new status
     * @param int $id The ID of the user role to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $role = UserRole::findOrFail($id);
            $role->is_active = $request->is_active;
            $role->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}