<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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
     * Display a listing of categories with optional filtering.
     *
     * @param Request $request The HTTP request object containing filter parameters
     * @param int|null $id Optional division ID to filter categories by
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with categories or JSON response for AJAX requests
     */
    public function index(Request $request, $id)
    {   
        $query = Category::with('division');

        // If ID parameter is provided, filter by division_id
        if ($id) {
            $query->where('division_id', $id);
        }

        // Search filters
        if ($request->filled('category_name')) {
            $query->where('category_name', 'like', '%'.$request->input('category_name').'%');
        }

        // Additional division filter from search form
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $categories = $query->orderBy('category_name')->paginate(10);
        $divisions = Division::where('status', '1')->orderBy('division_name')->get();

        $divisionName = null;
        if ($id) {
            $division = Division::find($id);
            $divisionName = $division ? $division->division_name : null;
        }

        if ($request->ajax()) {
            return view('admin.categories.partials.table', compact('categories'))->render();
        }

        return view('admin.categories.index', compact('categories', 'divisions', 'id' , 'divisionName'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @param int|null $id Optional division ID to pre-select in the form
     * @return \Illuminate\View\View Returns the category creation form view
     */
    public function create($id)
    {   
        $divisions = Division::where('status', '1')->orderBy('division_name')->get();
        return view('admin.categories.create', compact('divisions','id'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param Request $request The HTTP request containing category data
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:divisions,id',
            'category_name' => 'required|string|max:255|unique:categories',
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $category = new Category();
        $category->division_id = $request->division_id;
        $category->category_name = strtoupper($request->category_name);
        $category->status = $request->status;
        $category->created_by = auth()->id();
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'redirect' => route('admin.categories.index')
        ]);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param int $id The ID of the category to edit
     * @return \Illuminate\View\View Returns the category edit form view
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     *
     * @param Request $request The HTTP request containing updated category data
     * @param int $id The ID of the category to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255|unique:categories,category_name,' . $id,
            'status' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $category = Category::findOrFail($id);
        $category->category_name = strtoupper($request->category_name);
        $category->status = $request->status;
        $category->updated_by = auth()->id();
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'redirect' => route('admin.categories.index', ['id' => $request->division_id])
        ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param int $id The ID of the category to delete
     * @return \Illuminate\Http\RedirectResponse Redirects to category index with success message
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Update the status of the specified category.
     *
     * @param Request $request The HTTP request containing the new status
     * @param int $id The ID of the category to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $status = $request->status; // 1 = Active, 2 = Inactive
            $userId = auth()->id();

            $category = Category::findOrFail($id);
            $division = Division::findOrFail($category->division_id);

            // If trying to activate the category but its division is inactive, block it
            if ($status == 1 && $division->status == 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate this category because its division is inactive.'
                ]);
            }

            // Update category status
            $category->status = $status;
            $category->updated_by = $userId;
            $category->save();

            // If category is being inactivated, also inactivate all related products
            
            Product::where('category_id', $category->id)
                ->update([
                    'status' => 2,
                    'updated_by' => $userId,
                    'updated_at' => now()
                ]);
            

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get categories based on the division selected.
     */
    public function getCategoriesByDivision(Request $request)
    {
        $divisionId = $request->input('division_id');

        // Fetch categories that belong to the selected division
        $categories = Category::where('division_id', $divisionId)->get();

        // Return categories as JSON response
        return response()->json([
            'categories' => $categories
        ]);
    }
    
}