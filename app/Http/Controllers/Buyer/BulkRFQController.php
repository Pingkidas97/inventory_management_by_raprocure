<?php

namespace App\Http\Controllers\Buyer;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BranchDetail;
use App\Models\Category;
use App\Models\LiveVendorProduct;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\Uom;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Cache;

use App\Traits\HasModulePermission;


class BulkRFQController extends Controller
{
    use HasModulePermission;
    public function index(Request $request)
    {
        $this->ensurePermission('GENERATE_BULK_RFQ', 'add', '1');
        $user_branch_id_only = getBuyerUserBranchIdOnly();
        $branches = getBuyerBranchs($user_branch_id_only);
        $uom = DB::table('uoms')->get();
        
        $parent_details = getParentCompanyDetails();
        $is_indent_number_disabled = false;
        if($parent_details->is_api_enable==1 || $parent_details->is_inventory_enable==1){
            $is_indent_number_disabled = true;
        }
        return view('buyer.rfq.bulk-rfq.index', compact('branches', 'uom', 'is_indent_number_disabled'));
    }

    public function sanitize($value)
    {

        if (is_string($value)) {
            // 1. Remove HTML tags
            $value = strip_tags($value);
            // 2. Trim spaces
            $value = trim($value);
            // 3. Remove special characters (keep letters, numbers, space, dash, underscore)
            $value = preg_replace("/[^a-zA-Z0-9\s\-_]/", '', $value);
        }
        return $value;
    }
    public function uploadBulkExcel(Request $request)
    {
        //Log::info('now coming in the upload');

        $file = $request->file('bulk_rfq_excel');

        if ($file->getClientOriginalExtension() !== 'xlsx') {
            return response()->json([
                'status' => 3,
                'message' => 'Only Excel file Allowed to upload Bulk RFQ'
            ]);
        }

        $productData = $this->excelToArray($file);
        
        
        if (empty($productData['all_product_data'])) {
            return response()->json([
                'status' => 3,
                'all_product_data' => $productData,
                'message' => 'No Product Found to import Bulk RFQ....'
            ]);
        }else{
            $html = view('buyer.rfq.bulk-rfq.product-row', [
                'other_category_product_data' => $productData['all_product_data']
            ])->render();
            //dd($html);
            return response()->json([
                'status' => 2,
                'html' => $html,
                // 'all_product_data' => $productData,
                'message' => 'Uploaded Product Verified successfully'
            ]);
        }
    }


    private function excelToArray($file)
    {

        $data = Excel::toArray([], $file);
        $highestRow = count($data[0]);
        $allProductData = [];
        for ($row = 1; $row < $highestRow; $row++) {
            $productName = trim($data[0][$row][1]);

            $productName = preg_replace('/[^A-Za-z0-9\-,. ]/', '', $productName);

            if (!empty($productName)) {
                $brand = trim($data[0][$row][2]);
                $remarks = trim($data[0][$row][3]);
                $specification = trim($data[0][$row][4]);
                $size = trim($data[0][$row][5]);
                $quantity = (int) $data[0][$row][6];
                $uom = trim($data[0][$row][7]);;
                // $specification = sanetiz_all_string_data($specification, "encode");
                $specification = substr($specification, 0, 255);
                // $size = sanetiz_all_string_data($size, "encode");
                $brand = cleanGlobalSpecialChar($brand);
                // $remarks = sanetiz_all_string_data($remarks, "encode");
                $dataProduct = [
                    'product_name' => $productName,
                    'brand' => $brand,
                    'remarks' => $remarks,
                    'specification' => $specification,
                    'size' => $size,
                    'quantity' => $quantity,
                    'uom' => $uom,
                    'uom_id' => $this->getUOMIdByName($uom),
                    'uom_list' => $this->getUomlists()
                ];

                $allProductData[] = $dataProduct;
            }
        }

        //print_r($allProductData); die();
        // Take only first 100
        $allProductData = array_slice($allProductData, 0, 150);
        $productDetails = '';
        foreach ($allProductData as $key => $value) {

            if (strlen($value['product_name']) > 2) {
                $validPName = explode(',', $value['product_name']);

                if (count($validPName) > 1) {
                    $allProductData[$key]['product_name'] = trim($validPName[0]);
                    $extra = trim($validPName[1]);
                    $allProductData[$key]['specification'] .= !empty($extra) ? ', ' . $extra : '';
                }

                $product_name = strtolower($allProductData[$key]['product_name']);

                
                $result = $this->getProductByNameNEWOne($product_name);
                
                
                if ($result) {
                    if (count($result) > 1) {
                        $prodsArray = array_column($result, 'product_name');
                        $matched_p_name = mng_best_match_for_multiple($product_name, $prodsArray);
                        
                        if ($matched_p_name !== '') {
                            $productDetails =  DB::table('view_live_vend_with_alias')
                                ->where('product_name', $matched_p_name)
                                ->select('product_id', 'product_name', 'division_id', 'category_id')
                                ->first();
                        } else {
                            $productDetails = '';
                        }
                    } else {
                        $productDetails = $result[0];
                    }
                }
                
               
                if ($productDetails && $productDetails->product_name) {
                    $allProductData[$key]['product_name'] = $productDetails->product_name;
                    $allProductData[$key]['status'] = 1;
                    $allProductData[$key]['message'] = "Product Verified";
                } else {
                    $allProductData[$key]['status'] = 2;
                    $allProductData[$key]['message'] = "Invalid Product";
                }
            } else {
                $allProductData[$key]['status'] = 2;
                $allProductData[$key]['message'] = "Invalid Product";
            }
        }
        $seen = [];

        foreach ($allProductData as $index => $product) {

            $dupKey = strtolower(trim($product['product_name'])) . '|' .
                    strtolower(trim($product['specification'])) . '|' .
                    strtolower(trim($product['size']));

            if (isset($seen[$dupKey])) {

                // mark current duplicate row
                $allProductData[$index]['status'] = 3;
                $allProductData[$index]['message'] = "Duplicate Product";

                // OPTIONAL: also mark the first occurrence
                // $firstIndex = $seen[$dupKey];
                // $allProductData[$firstIndex]['status'] = 2;
                // $allProductData[$firstIndex]['message'] = "Duplicate Product";

            } else {
                $seen[$dupKey] = $index;
            }
        }
        
        return [
            'all_product_data' => $allProductData
        ];
    }

    private function getUomlists()
    {
        $uomList = Uom::where('status', 1)
                  ->orderBy('id', 'asc')
                  ->get();

        $list = [];

        foreach ($uomList as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->uom_name];
        }

        return compact('list');
    }

    private function getUOMIdByName($uom){
        if (empty($uom)) {
            return null; 
        }
        $uomRecord = Uom::where('status', 1)
                    ->where('uom_name', 'like', '%' . $uom . '%')
                    ->select('id')
                    ->first();
        if (!empty($uomRecord)) {
            return $uomRecord->id;
        } else {
            return findUomByUomAlias($uom) ?: null;
        }
    }

    public function getAllUOMLists(){
        $allUOMLists = $this->getUomlists();
        return response()->json([
                'status' => 2,
                'all_uom_list' => $allUOMLists
            ]);
    }
    
    public function validateProductName(Request $request)
    {
        $product_name = $request->input('p_name');
        $filtered_total_count = $request->input('filtered_total_count');
        
        // If filtered_total_count is '0', search for an exact match in product names
        if ($filtered_total_count == '0') {
            $products = DB::table('view_live_vend_with_alias')
                ->select('product_id', 'product_name')
                ->where('product_name', '=', $product_name)
                ->groupBy('product_id', 'product_name')
                ->get();

            if ($products->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'num_rows' => 0,
                    'products' => $products
                ]);
            }
        }

        // Pagination Setup
        $page_no = $request->input('page_no', 1);  // Default to page 1 if not provided
        $limit = 15;
        $offset = $limit * ($page_no - 1);

        // Prepare conditions for searching in product names
        $search_key_arr = explode(' ', $product_name);
        $query = DB::table('view_live_vend_with_alias')
            ->select('product_id', 'product_name')
            ->groupBy('product_id', 'product_name');

        // Add each search term to the WHERE clause using orWhere
        foreach ($search_key_arr as $srchrow) {
            $query->orWhere('product_name', 'like', '%' . $srchrow . '%');
        }

        // If filtered_total_count is '0', calculate the total number of rows
        if ($filtered_total_count == '0') {
            $number_filter_row = $query->count();
        } else {
            $number_filter_row = $filtered_total_count;
        }

        // Apply pagination and get results
        if ($number_filter_row > 0) {
            $result_data = $query->offset($offset)->limit($limit)->get();

            return response()->json([
                'status' => true,
                'num_rows' => $number_filter_row,
                'products' => $result_data
            ]);
        }

        // If no products found
        return response()->json([
            'status' => false,
            'message' => 'Invalid Product'
        ]);
    }
    

    function bulkDraftRFQ(Request $request) {
        $this->ensurePermission('GENERATE_BULK_RFQ', 'add', '1');
        // dd($request->all());
        $prn_no = cleanGlobalSpecialChar($request->prnNumber);
        $buyer_branch = $request->branch_id;
        if (empty($buyer_branch)) {
            $buyer_branch = 0;
        }
        $last_date_of_reponse = $request->last_date_to_response;
        $last_resp_dt = null;
        if (!empty($last_date_of_reponse)) {
            $last_resp_dt = isset($last_date_of_reponse) && !empty($last_date_of_reponse) ? changeCustomDateFormate($last_date_of_reponse) : '';
        }

        // $rfq_draft_id = 'D'.time().rand(1000, 9999);
        // $userdata = Auth::user()->id;
        $buyer_user_id  = Auth::user()->id;
        $buyer_id = getParentUserId();



        DB::beginTransaction();
        try {
            $rfq = new Rfq();
            $rfq->rfq_id = '';
            $rfq->buyer_id = $buyer_id;
            $rfq->buyer_user_id = $buyer_user_id;
            $rfq->buyer_branch = $buyer_branch;
            $rfq->prn_no = $prn_no;
            $rfq->last_response_date = $last_resp_dt;
            $rfq->record_type = 1;
            $rfq->is_bulk_rfq = 1;
            $rfq->buyer_rfq_status = 1;
            $rfq->save();
            $rfq->rfq_id = generateRFQDraftNumber($rfq->id);
            $rfq->save();

            $products = $request->input('product_name');
            $pr_status = $request->input('status');
            $i = 0;
            $product_order = 1;
            $variantOrderMap = [];
            
            foreach ($products as $key=>$val) {
                $productName = $val ?? null;
                
                if (!empty($productName) && $pr_status[$key] == 1) {
                    $product = Product::where('status', 1)
                                ->where('product_name', $productName)
                                ->first();
                    // Ensure the product exists on RFQs
                    if ($product && $product->id > 0) {
                        $alreadyExists = RfqProduct::where('rfq_id', $rfq->rfq_id)
                            ->where('product_id', $product->id)
                            ->exists();

                        if (!$alreadyExists) {
                            $rfqProduct = new RfqProduct();
                            $rfqProduct->rfq_id = $rfq->rfq_id;
                            $rfqProduct->product_id = $product->id;
                            $rfqProduct->brand = $request->input('brand.' . $i) ? cleanGlobalSpecialChar($request->input('brand.' . $i)) : null;
                            $rfqProduct->remarks = $request->input('remarks.' . $i) ?? null;
                            $rfqProduct->product_order = $product_order++;
                            $rfqProduct->save();
                        }
                        $variant_grp_id = now()->timestamp . mt_rand(10000, 99999);

                        if (!isset($variantOrderMap[$product->id])) {
                            $variantOrderMap[$product->id] = 1;
                        } else {
                            $variantOrderMap[$product->id]++;
                        }
                        $rfqProductVariant = new RfqProductVariant();
                        $rfqProductVariant->rfq_id = $rfq->rfq_id;
                        $rfqProductVariant->product_id = $product->id;
                        $rfqProductVariant->specification = $request->input('specification.' . $i) ?? null;
                        $rfqProductVariant->size = $request->input('size.' . $i) ?? null;
                        $rfqProductVariant->uom = $request->input('uom.' . $i) ?? null;
                        $rfqProductVariant->quantity = $request->input('quantity.' . $i) ?? null;
                        $rfqProductVariant->variant_order = $variantOrderMap[$product->id];
                        $rfqProductVariant->variant_grp_id = $variant_grp_id;
                        $rfqProductVariant->save();
                    }
                }
                $i++;
            }
            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => 'RFQ Draft created',
                'url' => route('buyer.rfq.compose-draft-rfq', ['draft_id' =>$rfq->rfq_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to create RFQ Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }
    
    public function getProductByNameNEWOne($product_name, $return_type = false)
    {
        $product_name = sanitizePNameForBulkProduct($product_name);

        // Exact match for product name
        $exact_product = $this->matchExactProductName($product_name);
        if (!empty($exact_product)) {
            return $exact_product;
        }

        $exact_alias = $this->matchExactAliasName($product_name);
        if (!empty($exact_alias)) {
            return $exact_alias;
        }

        // Search product name with like and 'AND' condition
        $like_product = $this->matchLikeProductName($product_name);
        if (!empty($like_product)) {
            return $like_product;
        }

        // Exact match for alias name
        

        // Search alias name with word like and 'AND' condition
        $like_alias = $this->matchLikeAliasName($product_name);
        if (!empty($like_alias)) {
            return $like_alias;
        }

        // If no products found
        return 0;
    }

    public function matchExactProductName($product_name)
    {
        return DB::table('view_live_vend_with_alias')
            ->where('product_name', $product_name)
            ->groupBy('product_id', 'product_name', 'division_id', 'category_id')
            ->select('product_id', 'product_name', 'division_id', 'category_id')
            ->get()
            ->toArray();
    }

    public function matchExactAliasName($product_name)
    {
        return DB::table('view_live_vend_with_alias')
            ->where('alias', $product_name)
            ->groupBy('product_id', 'product_name', 'division_id', 'category_id')
            ->select('product_id', 'product_name', 'division_id', 'category_id')
            ->get()
            ->toArray();
    }

    public function matchLikeProductName($product_name)
    {
        $product_name = normalizeSingularPlural($product_name);
        $words = explode(' ', $product_name);
        
        $query = DB::table('view_live_vend_with_alias')
            ->select('product_id', 'product_name', 'division_id', 'category_id')
            ->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    $query->Where('product_name', 'like', '%' . $word . '%');
                }
            })
            ->groupBy('product_id', 'product_name', 'division_id', 'category_id');
        
        return $query->get()->toArray();
    }

    public function matchLikeAliasName($product_name)
    {
        $product_name = normalizeSingularPlural($product_name);
        $words = explode(' ', $product_name);
        
        $query = DB::table('view_live_vend_with_alias')
            ->select('product_id', 'product_name', 'division_id', 'category_id')
            ->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    $query->Where('alias', 'like', '%' . $word . '%');
                }
            })
            ->groupBy('product_id', 'product_name', 'division_id', 'category_id');

        return $query->get()->toArray();
    }

    protected function searchQuery($query){
        $words = preg_split('/\s+/', sanitizePNameForBulkProduct($query));

        $likeConditions = [];
        foreach ($words as $word) {
            if (!empty($word)) {
                $likeConditions[] = "products.product_name LIKE '%" . addslashes($word) . "%'";
            }
        }

        $allWordsLike = count($likeConditions) ? implode(' AND ', $likeConditions) : "1=0";
        $someWordsLike = count($likeConditions) ? implode(' OR ', $likeConditions) : "1=0";

        $likeQuery = '%' . implode('%', $words) . '%';
        $startsWith = addslashes($words[0]) . '%';

        $aliasAllWords = implode(' AND ', array_map(fn($w) => "pa.alias LIKE '%$w%'", $words));
        $aliasSomeWords = implode(' OR ', array_map(fn($w) => "pa.alias LIKE '%$w%'", $words));

        $baseQuery = Product::select(
            'products.id',
            'products.product_name',
            'products.category_id',
            'products.division_id',
            DB::raw("
            (
                CASE
                    /* Exact Match Priority */
                    WHEN products.product_name = '$query' THEN 100

                    /* Alias EXACT */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND pa.alias = '$query'
                    ) THEN 98


                    /* ALL WORDS LIKE (Product) */
                    WHEN $allWordsLike THEN 95

                    /* ALL WORDS LIKE (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND ($aliasAllWords)
                    ) THEN 90




                    /* STARTS WITH (Product) */
                    WHEN products.product_name LIKE '$startsWith' THEN 88

                    /* STARTS WITH (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND pa.alias LIKE '$startsWith'
                    ) THEN 86







                     /* Fulltext (Product) */
                    WHEN MATCH(products.product_name) AGAINST('$query' IN BOOLEAN MODE) THEN 80

                     /* Fulltext (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND MATCH(pa.alias) AGAINST('$query' IN BOOLEAN MODE)
                    ) THEN 78



                    /* SOME WORDS LIKE (Product) */
                    WHEN $someWordsLike THEN 70

                    /* SOME WORDS LIKE (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND ($aliasSomeWords)
                    ) THEN 68

                    /* SOUNDEX Similarity (Product) */
                    WHEN SOUNDEX(products.product_name) = SOUNDEX('$query') THEN 60

                    /* SOUNDEX Similarity (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND SOUNDEX(pa.alias) = SOUNDEX('$query')
                    ) THEN 58

                    /* Weak contains (Product) */
                    WHEN products.product_name LIKE '$likeQuery' THEN 40

                    /* Weak contains (Alias) */
                    WHEN EXISTS (
                        SELECT 1 FROM product_alias pa
                        WHERE pa.product_id = products.id
                        AND pa.alias LIKE '$likeQuery'
                    ) THEN 38

                    ELSE 0
                END
            ) AS relevance_score
        ")
        )
            ->leftJoin('product_alias', 'product_alias.product_id', '=', 'products.id')
            ->with([
                'category:id,category_name',
                'division:id,division_name',
                'product_aliases:id,product_id,alias,alias_of,is_new'
            ])
            ->where('products.status', 1)
            ->where(function ($q) use ($query, $likeQuery) {

                /* Fulltext Product */
                $q->whereRaw("MATCH(products.product_name) AGAINST(? IN BOOLEAN MODE)", [$query])
                    ->orWhere('products.product_name', 'LIKE', $likeQuery);

                /* Alias Search */
                $q->orWhereHas('product_aliases', function ($aliasQuery) use ($query, $likeQuery) {
                    $aliasQuery->whereRaw("MATCH(alias) AGAINST(? IN BOOLEAN MODE)", [$query])
                        ->orWhere('alias', 'LIKE', $likeQuery);
                });
            })
            ->groupBy('products.id',
            'products.product_name',
            'products.category_id',
            'products.division_id')
            ->orderByDesc('relevance_score');

        // dd($baseQuery->toRawSql());
        // ->orderBy('products.product_name', 'asc'); // Fallback sort

        return $baseQuery;
    }

    public function searchProduct(Request $request)
    {
        $query  = trim($request->query('query', ''));
        $page   = max((int)$request->query('page', 1), 1);
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        if (strlen($query) < 2) {
            return response()->json(['data' => [], 'hasMore' => false]);
        }

        $cacheKey = "product_search_{$query}_page_{$page}";

        $result = Cache::remember($cacheKey, 60, function () use ($query, $limit, $offset) {

            $query = $this->sanitize($query);
            $baseQuery = clone $this->searchQuery($query);
            $products = $baseQuery->skip($offset)->take($limit)->get();

            $formatted = $products->map(function ($product) {
                return [
                    'id'            => $product->id,
                    'product_name'  => $product->product_name,
                    'category_id'   => $product->category_id,
                    'division_id'   => $product->division_id,
                    'category_name' => optional($product->category)->category_name,
                    'division_name' => optional($product->division)->division_name,
                ];
            });

            return [
                'data' => $formatted,
                'hasMore' => $products->count() === $limit,
            ];
        });

        return response()->json($result);
    }
}
