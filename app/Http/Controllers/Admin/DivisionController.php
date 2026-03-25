<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DivisionController extends Controller
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
     * Display a listing of divisions with optional filtering.
     *
     * @param Request $request The HTTP request object containing filter parameters
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with divisions or JSON response for AJAX requests
     */
    public function index(Request $request)
    {
        $query = Division::query();

        if ($request->division_name) {
            $query->where('division_name', 'like', '%' . $request->division_name . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $divisions = $query->orderBy('id', 'desc')->paginate(25);

        // If AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('admin.divisions.partials.table', compact('divisions'))->render();
        }

        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Show the form for creating a new division.
     *
     * @return \Illuminate\View\View Returns the division creation form view
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * Store a newly created division in storage.
     *
     * @param Request $request The HTTP request containing division data
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_name' => 'required|string|max:255|unique:divisions,division_name',
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $division = new Division();
            $division->division_name = strtoupper($request->division_name); // Convert to uppercase
            $division->status = $request->status;
            $division->created_by = Auth::id();
            $division->updated_by = Auth::id();
            $division->save();

            return response()->json([
                'success' => true,
                'message' => 'Division created successfully',
                'redirect' => route('admin.divisions.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating division: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified division.
     *
     * @param int $id The ID of the division to edit
     * @return \Illuminate\View\View Returns the division edit form view
     */
    public function edit($id)
    {
        $division = Division::findOrFail($id);
        return view('admin.divisions.edit', compact('division'));
    }

    /**
     * Update the specified division in storage.
     *
     * @param Request $request The HTTP request containing updated division data
     * @param int $id The ID of the division to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'division_name' => 'required|string|max:255|unique:divisions,division_name,' . $id,
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $division = Division::findOrFail($id);

            // Set values manually
            $division->division_name = strtoupper($request->division_name); // Convert to uppercase
            $division->status = $request->status;
            $division->updated_by = Auth::id();

            $division->save();

            return response()->json([
                'success' => true,
                'message' => 'Division updated successfully',
                'redirect' => route('admin.divisions.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating division: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified division from storage.
     *
     * @param int $id The ID of the division to delete
     * @return \Illuminate\Http\RedirectResponse Redirects to division index with success message
     */
    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return redirect()->route('admin.divisions.index')->with('success', 'Division deleted successfully.');
    }

    /**
     * Update the status of the specified division.
     *
     * @param Request $request The HTTP request containing the new status
     * @param int $id The ID of the division to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $status = $request->status;
            $userId = auth()->id();

            // Fetch Division
            $division = Division::findOrFail($id);
            $division->status = $status;
            $division->updated_by = $userId;
            $division->save();

            // Update Categories under this division
            $categories = Category::where('division_id', $id)->get();
            foreach ($categories as $category) {
                $category->status = $status;
                $category->updated_by = $userId;
                $category->save();
            }

            // Update Products under those categories
            $categoryIds = $categories->pluck('id')->toArray();

            $products = Product::where('division_id', $id)
                ->whereIn('category_id', $categoryIds)
                ->get();

            foreach ($products as $product) {
                $product->status = $status;
                $product->updated_by = $userId;
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Status update failed: ' . $e->getMessage(),
            ]);
        }
    }

}