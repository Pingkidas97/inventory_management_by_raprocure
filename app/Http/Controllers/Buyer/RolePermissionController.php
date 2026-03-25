<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserRole;
use App\Models\Module;
use App\Models\UserRoleModulePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Traits\HasModulePermission;
class RolePermissionController extends Controller
{
    use HasModulePermission;
     /**
     * Constructor to check user authorization.
     */
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 1) {
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
        $this->ensurePermission('MANAGE_ROLE', 'view', '1');
        $results = UserRole::where('role_name_for', 1)->where('user_master_id', getParentUserId())->paginate(25);
        return view('buyer.role-permission.index', compact('results'));
    }
    /**
     * Show the form for creating a new user role.
     *
     * @return \Illuminate\View\View Returns the user role creation form with modules and their permissions
    */
    public function create()
    {
        $this->ensurePermission('MANAGE_ROLE', 'add', '1');
        $modules = Module::where('module_for', 1)
            ->where('is_active', 1)
            ->orderBy('is_order')
            ->get()
            ->map(function($module) {
                // Define restricted slugs for different permission types
            $restrictedSlugsForAdd = ['EDIT_RFQ',
                                        'ACTIVE_RFQS_CIS',
                                        'TECHNICAL_APPROVAL_WITH_PRICE',
                                        'COUNTER_OFFER_RFQ',
                                        'SENT_RFQ',
                                        'ORDERS_CONFIRMED_LISTING',
                                        'UNAPPROVE_PO_LISTING',
                                        'VENDORS_SEARCH',
                                        'REPORTS_TOTAL_RFQ_CREATED',
                                        'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                        'REPORTS_PRODUCT_ORDERED_DETAILS',
                                        'REPORTS_VENDOR_WISE_ACTIVITY',
                                        'MY_PROFILE',
                                        'CHANGE_PASSWORD'];

            $restrictedSlugsForEdit = ['GENERATE_NEW_RFQ',
                                    'GENERATE_BULK_RFQ',
                                    'TECHNICAL_APPROVAL',
                                    'TECHNICAL_APPROVAL_WITH_PRICE',
                                    'COUNTER_OFFER_RFQ',
                                    'TO_GENERATE_UNAPPROVE_PO',
                                    'TO_CONFIRM_ORDER',
                                    'CANCEL_ORDER',
                                    'CLOSE_RFQ',
                                    'ORDERS_CONFIRMED_LISTING',
                                    'UNAPPROVE_PO_LISTING',
                                    'VENDORS_SEARCH',
                                    'FAVOURITE_VENDORS',
                                    'BLACKLISTED_VENDORS',
                                    'REPORTS_TOTAL_RFQ_CREATED',
                                    'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                    'REPORTS_PRODUCT_ORDERED_DETAILS',
                                    'REPORTS_VENDOR_WISE_ACTIVITY',
                                    'INDENT_APPROVE',
                                    'FORCE_CLOSURE',
                                    'GATE_PASS_ENTRY'];//pingki

            $restrictedSlugsForDelete = ['GENERATE_NEW_RFQ',
                                        'GENERATE_BULK_RFQ',
                                        'EDIT_RFQ',
                                        'ACTIVE_RFQS_CIS',
                                        'TECHNICAL_APPROVAL',
                                        'TECHNICAL_APPROVAL_WITH_PRICE',
                                        'COUNTER_OFFER_RFQ',
                                        'TO_GENERATE_UNAPPROVE_PO',
                                        'TO_CONFIRM_ORDER',
                                        'CANCEL_ORDER',
                                        'CLOSE_RFQ',
                                        'SENT_RFQ',
                                        'ORDERS_CONFIRMED_LISTING',
                                        'VENDORS_SEARCH',
                                        'REPORTS_TOTAL_RFQ_CREATED',
                                        'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                        'REPORTS_PRODUCT_ORDERED_DETAILS',
                                        'REPORTS_VENDOR_WISE_ACTIVITY',
                                        'MY_PROFILE',
                                        'MANAGE_USERS',
                                        'MANAGE_ROLE',
                                        'CHANGE_PASSWORD',
                                        'INDENT_APPROVE',
                                        'FORCE_CLOSURE',
                                        'GATE_PASS_ENTRY'];//pingki

            $restrictedSlugsForView = ['GENERATE_NEW_RFQ',
                                    'GENERATE_BULK_RFQ',
                                    'EDIT_RFQ',
                                    'TECHNICAL_APPROVAL',
                                    'TO_GENERATE_UNAPPROVE_PO',
                                    'CANCEL_ORDER',
                                    'CLOSE_RFQ',
                                    'CHANGE_PASSWORD',
                                    'INDENT_APPROVE',
                                    'FORCE_CLOSURE',
                                    'GATE_PASS_ENTRY'];//pingki
                // Set available permissions based on slug restrictions
                $module->available_permissions = [
                    'add' => !in_array($module->module_slug, $restrictedSlugsForAdd),
                    'edit' => !in_array($module->module_slug, $restrictedSlugsForEdit),
                    'delete' => !in_array($module->module_slug, $restrictedSlugsForDelete),
                    'view' => !in_array($module->module_slug, $restrictedSlugsForView)
                ];
                return $module;
            });
        return view('buyer.role-permission.create', compact('modules'));
    }
    /**
     * Store a newly created user role in storage.
     *
     * @param Request $request The HTTP request containing role data and permissions
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        $this->ensurePermission('MANAGE_ROLE', 'add', '1');

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
            ->where('role_name_for',1)
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
            $userRole->role_name_for =1;
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
        $this->ensurePermission('MANAGE_ROLE', 'edit', '1');
        $userRole = UserRole::with('permissions')->findOrFail($id);
        // Get all active modules for role management (module_for = 3)
        $modules = Module::where('module_for', 1)
            ->where('is_active', 1)
            ->orderBy('is_order')
            ->get()
            ->map(function($module) use ($userRole) {
                // Define restricted slugs for different permission types
            $restrictedSlugsForAdd = ['EDIT_RFQ',
                                        'ACTIVE_RFQS_CIS',
                                        'TECHNICAL_APPROVAL_WITH_PRICE',
                                        'COUNTER_OFFER_RFQ',
                                        'SENT_RFQ',
                                        'ORDERS_CONFIRMED_LISTING',
                                        'UNAPPROVE_PO_LISTING',
                                        'VENDORS_SEARCH',
                                        'REPORTS_TOTAL_RFQ_CREATED',
                                        'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                        'REPORTS_PRODUCT_ORDERED_DETAILS',
                                        'REPORTS_VENDOR_WISE_ACTIVITY',
                                        'MY_PROFILE',
                                        'CHANGE_PASSWORD'];

            $restrictedSlugsForEdit = ['GENERATE_NEW_RFQ',
                                    'GENERATE_BULK_RFQ',
                                    'TECHNICAL_APPROVAL',
                                    'TECHNICAL_APPROVAL_WITH_PRICE',
                                    'COUNTER_OFFER_RFQ',
                                    'TO_GENERATE_UNAPPROVE_PO',
                                    'TO_CONFIRM_ORDER',
                                    'CANCEL_ORDER',
                                    'CLOSE_RFQ',
                                    'ORDERS_CONFIRMED_LISTING',
                                    'UNAPPROVE_PO_LISTING',
                                    'VENDORS_SEARCH',
                                    'FAVOURITE_VENDORS',
                                    'BLACKLISTED_VENDORS',
                                    'REPORTS_TOTAL_RFQ_CREATED',
                                    'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                    'REPORTS_PRODUCT_ORDERED_DETAILS',
                                    'REPORTS_VENDOR_WISE_ACTIVITY',
                                    'INDENT_APPROVE',
                                    'FORCE_CLOSURE',
                                    'GATE_PASS_ENTRY'//pingki
                                ];

            $restrictedSlugsForDelete = ['GENERATE_NEW_RFQ',
                                        'GENERATE_BULK_RFQ',
                                        'EDIT_RFQ',
                                        'ACTIVE_RFQS_CIS',
                                        'TECHNICAL_APPROVAL',
                                        'TECHNICAL_APPROVAL_WITH_PRICE',
                                        'COUNTER_OFFER_RFQ',
                                        'TO_GENERATE_UNAPPROVE_PO',
                                        'TO_CONFIRM_ORDER',
                                        'CANCEL_ORDER',
                                        'CLOSE_RFQ',
                                        'SENT_RFQ',
                                        'ORDERS_CONFIRMED_LISTING',
                                        'VENDORS_SEARCH',
                                        'REPORTS_TOTAL_RFQ_CREATED',
                                        'REPORTS_ORDER_CONFIRMATION_SUMMARY',
                                        'REPORTS_PRODUCT_ORDERED_DETAILS',
                                        'REPORTS_VENDOR_WISE_ACTIVITY',
                                        'MY_PROFILE',
                                        'MANAGE_USERS',
                                        'MANAGE_ROLE',
                                        'CHANGE_PASSWORD',
                                        'INDENT_APPROVE',
                                        'FORCE_CLOSURE',
                                        'GATE_PASS_ENTRY'];//pingki

            $restrictedSlugsForView = ['GENERATE_NEW_RFQ',
                                    'GENERATE_BULK_RFQ',
                                    'EDIT_RFQ',
                                    'TECHNICAL_APPROVAL',
                                    'TO_GENERATE_UNAPPROVE_PO',
                                    'CANCEL_ORDER',
                                    'CLOSE_RFQ',
                                    'CHANGE_PASSWORD',
                                    'INDENT_APPROVE',
                                    'FORCE_CLOSURE',
                                    'GATE_PASS_ENTRY'];//pingki
                // Set available permissions based on slug restrictions
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
        return view('buyer.role-permission.edit', [
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
        $this->ensurePermission('MANAGE_ROLE', 'edit', '1');

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
            ->where('role_name_for', 1)
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
        $this->ensurePermission('MANAGE_ROLE', 'delete', '1');
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
        $this->ensurePermission('MANAGE_ROLE', 'edit', '1');
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
