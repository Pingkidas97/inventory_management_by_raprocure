<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserRoleMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;


class UserController extends Controller
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
     * Display a listing of users with optional filtering.
     *
     * @param Request $request The HTTP request object containing filter parameters
     * @return \Illuminate\View\View Returns a view with paginated users and filter values
     */
    public function index(Request $request)
    {
        // Initialize the query with eager loading of 'role'
        $query = User::with('role')
                    ->where('user_type', 3)
                    ->where(function($q) {
                        $q->whereNotNull('parent_id')
                          ->where('parent_id', '<>', '');
                    });

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply role filter if provided
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('id', $request->input('role'));
            });
        }

        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $users = $query->paginate($perPage)->appends($request->all());

      

        if ($request->ajax()) {
            return view('admin.users.partials.table', compact('users'))->render();
        }

        return view('admin.users.index', compact('users'));
    }

    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View Returns the user creation form with roles and countries
     */
    public function create()
    {
        $roles = UserRole::where('role_name_for', 3)->get();
        $countries = DB::table('countries')->select('name', 'phonecode')->get();
        return view('admin.users.create', compact('roles','countries'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request The HTTP request containing user data
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'designation' => 'required|string|max:255',
            'country_code' => 'required|string|max:5',
            'mobile' => 'required|string|max:15',
            'role_id' => 'required|exists:user_roles,id',
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            // Create and save the user using ORM save()
            $user = new User();
            $user->name = strtoupper($request->name);
            $user->email = $request->email;
            $user->password = Hash::make(123456);
            $user->designation = $request->designation;
            $user->country_code = $request->country_code;
            $user->mobile = $request->mobile;
            $user->status = $request->status;
            $user->user_type = '3';
            $user->parent_id = '2';
            $user->user_created_by = Auth::id();
            $user->user_updated_by = Auth::id();
            $user->save();

            // Create and save the role mapping using ORM save()
            $roleMapping = new UserRoleMapping();
            $roleMapping->user_id = $user->id;
            $roleMapping->user_role_id = $request->role_id;
            $roleMapping->is_active = 1;
            $roleMapping->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'redirect' => route('admin.users.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param int $id The ID of the user to edit
     * @return \Illuminate\View\View Returns the user edit form with user data, roles and countries
     */
    public function edit($id)
    {  
        $user = User::with('role_mapping')->findOrFail($id);
        $roles = UserRole::where('role_name_for', 3)->get();
        $countries = DB::table('countries')->select('name', 'phonecode')->get();
        return view('admin.users.edit', compact('user', 'roles', 'countries'));
    }

    /**
     * Update an existing user in storage.
     *
     * @param Request $request The HTTP request containing updated user data
     * @param int $id The ID of the user to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'designation' => 'required|string|max:255',
            'country_code' => 'required|string|max:5',
            'mobile' => 'required|string|max:15',
            'role_id' => 'required|exists:user_roles,id',
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            // Fetch the user by ID
            $user = User::findOrFail($id);

            // Update the user using ORM save()
            $user->name = strtoupper($request->name);
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->country_code = $request->country_code;
            $user->mobile = $request->mobile;
            $user->status = $request->status;
            $user->user_updated_by = Auth::id();
            $user->save();

            // Update or create the role mapping using ORM save()
            $roleMapping = UserRoleMapping::where('user_id', $user->id)->first();
            if ($roleMapping) {
                $roleMapping->user_role_id = $request->role_id;
                $roleMapping->is_active = $request->status == 1 ? 1 : 0;
                $roleMapping->save();
            } else {
                $roleMapping = new UserRoleMapping();
                $roleMapping->user_id = $user->id;
                $roleMapping->user_role_id = $request->role_id;
                $roleMapping->is_active = $request->status == 1 ? 1 : 0;
                $roleMapping->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'redirect' => route('admin.users.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param User $user The user model instance to delete
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of the specified user.
     *
     * @param Request $request The HTTP request containing the new status
     * @param int $id The ID of the user to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $role = User::findOrFail($id);
            $role->status = $request->is_active;
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

    public function changePassword(Request $request) {
        return view('admin.users.change-password');
    }
 
    public function updatePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ]);
        }

        try {
            $user = auth()->user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
            }   

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
                'redirect' => route('admin.dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }
}