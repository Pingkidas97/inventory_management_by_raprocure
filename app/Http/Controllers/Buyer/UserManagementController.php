<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BranchDetail;
use App\Models\UserRole;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\UserRoleMapping;
use DB;
use App\Traits\HasModulePermission;
use App\Helpers\EmailHelper;


class UserManagementController extends Controller
{
    use HasModulePermission;

    public function index(Request $request)
    {
        $this->ensurePermission('MANAGE_USERS', 'view', '1');
        // Initialize the query with eager loading of 'role'
        $query = User::with('role')
                    ->where('user_type',1)
                    ->where('parent_id',getParentUserId());
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
        $results = $query->paginate($perPage)->appends($request->all());
        // print_r( $results);die;
        if ($request->ajax()) {
            return view('buyer.user-management.partials.table', compact('results'))->render();
        }
        return view('buyer.user-management.index', compact('results'));
    }
     /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View Returns the user creation form with roles and countries
    */
    public function create()
    {
        $this->ensurePermission('MANAGE_USERS', 'add', '1');

        // $branches = BranchDetail::where('user_id', getParentUserId())->where('user_type', 1)->where('status', 1)->get();
        $branches = BranchDetail::where('user_id', getParentUserId())->where('record_type', 1)->where('is_regd_address', 2)->where('status', 1)->get();
        $roles = UserRole::where('role_name_for',1)->where('user_master_id', getParentUserId())->get();
        $countries=Country::select('name','phonecode')->where('status',1)->get();
        return view('buyer.user-management.create', compact('branches','countries','roles'));
    }
    /**
     * Store a newly created user in storage.
     *
     * @param Request $request The HTTP request containing user data
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        $this->ensurePermission('MANAGE_USERS', 'add', '1');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'designation' => 'required|string|max:255',
            'country' => 'required|string|max:5',
            'mobile_no' => 'required|string|max:15',
            'role_id' => 'required|exists:user_roles,id',
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        // $password = mt_rand(10000000, 99999999);
        $password = 12345678;

        try {
            DB::beginTransaction();
            // Create and save the user using ORM save()
            $user = new User();
            $user->name = strtoupper($request->name);
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->designation = $request->designation;
            $user->country_code = $request->country;
            $user->mobile = $request->mobile_no;
            $user->status = $request->status;
            $user->user_type = '1';
            $user->parent_id = getParentUserId();
            $user->user_created_by = Auth::id();
            $user->user_updated_by = Auth::id();
            $user->save();

            // Create and save the role mapping using ORM save()
            $roleMapping = new UserRoleMapping();
            $roleMapping->user_id = $user->id;
            $roleMapping->user_role_id = $request->role_id;
            $roleMapping->is_active = 1;
            $roleMapping->save();
            $this->updateBuyerUserBranch($user->id, $request->branchId);

            $notification_data = array();
            $notification_data['message_type'] = 'Buyer User Creation';
            $notification_data['notification_link'] = route("admin.dashboard");
            // $notification_data['to_user_id'] = getSADetails()->id;
            // sendNotifications($notification_data);
            
            DB::commit();

            // send mail to created user
            $mail_data = buyerEmailTemplet('when-buyer-register-user-email');
            $admin_msg = $mail_data->mail_message;
            $subject = $mail_data->subject;
            $bold_html_password = '<span style="font-weight: 600" >'.$password.'<span>';

            $admin_msg = str_replace('$name', strtoupper($request->name), $admin_msg);
            $admin_msg = str_replace('$email', $request->email, $admin_msg);
            $admin_msg = str_replace('$password', $bold_html_password, $admin_msg);
            $admin_msg = str_replace('$link', route("login"), $admin_msg);
            
            EmailHelper::sendMail($request->email, $subject, $admin_msg);

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
        $this->ensurePermission('MANAGE_USERS', 'edit', '1');

        $user=User::find($id);

        // $branches = BranchDetail::where('user_id', getParentUserId())->where('user_type', 1)->where('status', 1)->get();
        $branches = BranchDetail::where('user_id', getParentUserId())->where('record_type', 1)->where('is_regd_address', 2)->where('status', 1)->get();
        $roles = UserRole::where('role_name_for',1)->where('user_master_id', getParentUserId())->get();
        $countries=Country::select('name','phonecode')->where('status',1)->get();
        $userBranches=$this->getBuyerUserBranch($id);
        return view('buyer.user-management.edit', compact('user','branches','countries','roles','userBranches'));
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
        $this->ensurePermission('MANAGE_USERS', 'edit', '1');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'designation' => 'required|string|max:255',
            'country' => 'required|string|max:5',
            'mobile_no' => 'required|string|max:15',
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
            $user->country_code = $request->country;
            $user->mobile = $request->mobile_no;
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

            $this->updateBuyerUserBranch($user->id,$request->branchId);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'redirect' => route('buyer.user-management.users')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getBuyerUserBranch($buyerUserId)
    {
        $buyerUserBranch = DB::table('users_branch')
            ->select('branch_id')
            ->where('user_id', $buyerUserId)
            ->pluck('branch_id')
            ->toArray();
        return $buyerUserBranch;
    }

    private function updateBuyerUserBranch($userId,$branch)
    {
        DB::table('users_branch')->where('user_id', $userId)->delete();
        foreach ($branch as $key => $value) {
            DB::table('users_branch')->insert([
                'user_id' => $userId,
                'branch_id' => $value
            ]);
        }
    }
}
