<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BranchDetail;
use App\Models\Uom;
use App\Models\InventoryType;
use App\Models\Product;
use App\Models\TempInventoryMgt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Traits\TrimFields;
use App\Traits\HasModulePermission;
use App\Http\Controllers\Buyer\InventoryController;
use App\Http\Controllers\Buyer\BulkRFQController;
class BulkInventoryController extends Controller
{
    use TrimFields;
    use HasModulePermission;
    public function importBulkInventory()
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'view', '1');
            }
            $data['page_heading'] = "Import Bulk Inventory";
            session(['page_title' => 'Import Bulk Inventory - Raprocure']);
            $user_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
            DB::table('temp_inventory_mgt')->where('user_id', $user_id)->delete();
            $data['branch_data'] = BranchDetail::getDistinctActiveBranchesByUser($user_id);
            $data['uom_list'] = Uom::all();

            return view('buyer.bulkinventory.bulk_inventory', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uploadCSV(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'add', '1');
            }
            $user_id =  Auth::user()->id;
            DB::table('temp_inventory_mgt')->where('user_id', $user_id)->delete();
            $validator = Validator::make($request->all(), [
                'import_product' => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 0, 'message' => 'Invalid file uploaded.']);
            }

            $file = $request->file('import_product');

            // Memory-efficient CSV parsing
            $handle = fopen($file->getRealPath(), 'r');
            if (!$handle) {
                return response()->json(['status' => 2, 'message' => 'Unable to read CSV file']);
            }

            $headers = fgetcsv($handle);
            if (!$headers || count($headers) < 3) {
                return response()->json(['status' => 2, 'message' => 'CSV header is invalid or missing']);
            }

            $headers = array_map(fn($h) => str_replace(' ', '_', trim($h)), $headers);

            $uomList = $this->getUomMappings();

            $inventoryTypes = $this->getInventoryTypeMappings();

            $productNames = [];
            $rows = [];
            $rowCount = 0;
            $MAX_ROWS = 2000;

            // Phase 1: Read CSV line-by-line
            while (($row = fgetcsv($handle)) !== false) {
                if ($rowCount > $MAX_ROWS) {
                    fclose($handle);
                    return response()->json([
                        'status'  => 2,
                        'message' => 'CSV upload limit exceeded. Maximum allowed 2000 Products / Varriants at a time.'
                    ]);
                }
                $assocRow = array_combine($headers, $row);
                //if (!$assocRow || !isset($assocRow['Product_Name']) || !isset($assocRow['Product_Specification'])) continue;

                // Clean product name
                $productName = Str::of($assocRow['Product_Name'])->squish()->__toString();
                $productName = preg_replace('/[^A-Za-z0-9\-,. ]/', '', $productName);
                $assocRow['Product_Name'] = $productName;

                $ourProductName = Str::of($assocRow['Our_Product_Name'])->squish()->__toString();
                $ourProductName = preg_replace('/[^A-Za-z0-9\-,. ]/', '', $ourProductName);
                $assocRow['Product_Name'] = $productName;

                // Clean product specification (optional)
                $spec = Str::of($assocRow['Product_Specification'])->squish()->__toString();
                $size = Str::of($assocRow['Product_Size'])->squish()->__toString();

                // Create uniqueness key
                if($productName || $ourProductName){
                    $uniqueKey = $productName.'|'.$ourProductName.'|'. $spec .'|'.$size;
                }else{
                    continue;
                }
                // Avoid duplicates based on BOTH fields
                if (isset($uniqueProducts[$uniqueKey])) continue;

                $uniqueProducts[$uniqueKey] = true;

                $rows[] = $assocRow;
                $rowCount++;
            }
            //dd($uniqueProducts);
            //dd($productNames);
            fclose($handle);

            if (empty($rows)) {
                return response()->json(['status' => 2, 'message' => 'CSV file is empty']);
            }
            $bulkRFQController = app(BulkRFQController::class);

            // Fetch verified products only once
            $finalProductDataResult = [];
            $productDetails=[];
            //dd($uniqueProducts);
            foreach (array_keys($uniqueProducts) as $key => $name) {

                $temp_name = explode("|", $name);
                $searchName = $temp_name[0] ?: $temp_name[1];

                $finalProductDataResult = $bulkRFQController->getProductByNameNEWOne($searchName);
                // print_r($finalProductDataResult);

                if ($finalProductDataResult && count($finalProductDataResult) > 0) {

                    if (count($finalProductDataResult) > 1) {

                        $prodsArray = array_column($finalProductDataResult, 'product_name');
                        $matched_p_name = mng_best_match_for_multiple($searchName, $prodsArray);

                        if ($matched_p_name !== '') {
                            $finalP = DB::table('view_live_vend_with_alias')
                                ->where('product_name', $matched_p_name)
                                ->select('product_id', 'product_name', 'division_id', 'category_id')
                                ->first();
                        } else {
                            $finalP = (object)[
                                'product_id' => null,
                                'product_name' => null,
                                'division_id' => null,
                                'category_id' => null,
                            ];
                        }

                    } else {
                        $finalP = $finalProductDataResult[0];
                    }

                } else {

                    if ($searchName) {

                        $finalProductDataResult = DB::table('products')
                            ->where('product_name', 'LIKE', '%' . $searchName . '%')
                            ->select('id as product_id', 'product_name', 'division_id', 'category_id')
                            ->get();
                        // print_r($finalProductDataResult);
                        if ($finalProductDataResult && count($finalProductDataResult) > 1) {

                            $prodsArray = array_column($finalProductDataResult, 'product_name');
                            $matched_p_name = mng_best_match_for_multiple($searchName, $prodsArray);

                            if ($matched_p_name !== '') {
                                $finalP = DB::table('products')
                                    ->where('product_name', $matched_p_name)
                                    ->select('id as product_id', 'product_name', 'division_id', 'category_id')
                                    ->first();
                            } else {
                                $finalP = (object)[
                                    'product_id' => null,
                                    'product_name' => null,
                                    'division_id' => null,
                                    'category_id' => null,
                                ];
                            }

                        } else {
                            $finalP = $finalProductDataResult[0] ?? (object)[
                                'product_id' => null,
                                'product_name' => null,
                                'division_id' => null,
                                'category_id' => null,
                            ];
                        }

                    } else {
                        $finalP = (object)[
                            'product_id' => null,
                            'product_name' => null,
                            'division_id' => null,
                            'category_id' => null,
                        ];
                    }
                }

                // ALWAYS SET THIS KEY
                $finalP->our_product_name = $temp_name[1] ?? null;

                // PUSH ONLY ONCE
                $productDetails[] = $finalP;
            }
            //dd($productDetails); die();
            $response_data = [];
            $prod_name_arr = [];
            $result = [];
            // Phase 2: Chunk processing for speed + DB write
            foreach (array_chunk($rows, 2000) as $chunk) {
                $chunkResponseNew = [];

                foreach ($chunk as $key => $row) {
                    $row = $this->sanitizeProductRow($row);

                    // if (!isset($row['Product_Name'], $row['Product_UOM'], $row['Inventory_Type'])) {
                    //     continue;
                    // }
                    $row['Product_Name'] = Str::squish($row['Product_Name']);
                    //print_r($row);;
                    //echo $productDetails[$key]->product_name; die();
                    //if(!empty($productDetails[$key]->product_name)){
                        $result = $this->validateProductRow(
                            $row,
                            $key,
                            $productDetails[$key]->product_name,
                            $productDetails[$key]->our_product_name,
                            $productDetails[$key]->product_id,
                            $productDetails[$key]->division_id,
                            $productDetails[$key]->category_id,
                            $uomList['dataArr'],
                            $uomList['vls'],
                            $inventoryTypes['nms'],
                            $inventoryTypes['vls'],
                            $prod_name_arr
                        );

                        $response_data[] = $result['response'];
                        $chunkResponseNew[] = $result['response_new'];
                        $prod_name_arr = $result['prod_name_arr'];
                    //}
                }
                if (!empty($chunkResponseNew)) {
                    DB::table('temp_inventory_mgt')->insert($chunkResponseNew);
                }
            }
            //dd($response_data);
            return response()->json([
                'status' => 1,
                'message' => 'CSV file imported successfully',
                'uom_list' => $uomList['list'],
                'invt_type_list' => $inventoryTypes['list'],
                'data' =>  array_values($response_data),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getUomMappings()
    {
        $uomList = Uom::where('status', 1)->get();
        $list = $vls = $dataArr = $vlsById = [];

        foreach ($uomList as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->uom_name];
            $vls[strtolower($row->uom_name)] = $row->uom_name;
            $dataArr[strtolower($row->uom_name)] = $row->id;
            $vlsById[$row->id] = $row->uom_name;
        }

        return compact('list', 'vls', 'dataArr', 'vlsById');
    }

    private function getInventoryTypeMappings()
    {
        $inventoryTypes = InventoryType::where('status', 1)->get();
        $list = $vls = $nms = $vlsById = [];

        foreach ($inventoryTypes as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->name];
            $vls[strtolower($row->name)] = $row->id;
            $nms[$row->id] = $row->name;
            $vlsById[$row->id] = $row->name;
        }

        return compact('list', 'vls', 'nms', 'vlsById');
    }

    private function sanitizeProductRow($row)
    {
        $row['Product_Specification'] = mb_convert_encoding($row['Product_Specification'] ?? '', 'UTF-8', 'ISO-8859-1');
        $row['Product_Size'] = mb_convert_encoding($row['Product_Size'] ?? '', 'UTF-8', 'ISO-8859-1');
        $row['Brand'] = $this->remove_special_chars($row['Brand'] ?? '');
        $row['Our_Product_Name'] = $this->remove_special_chars($row['Our_Product_Name'] ?? '');
        $row['Inventory_Grouping'] = $this->remove_special_chars($row['Inventory_Grouping'] ?? '');
        $row['Cost_Center'] = $this->remove_special_chars($row['Cost_Center'] ?? '');
        return $row;
    }
    private function validateProductRow(
        $vals,
        $key,
        $final_prod_data,
        $final_our_product_name,
        $final_prod_id,
        $final_div_id,
        $final_cat_id,
        $uom_data_arr,
        $uom_vls,
        $invt_type_nms,
        $invt_type_vls,
        $prod_name_arr
    ) {
        //dd($vals);
        $error_code = '';
        $srno = $key + 1;

        $Product_Name1 = trim(preg_replace('/\s+/', ' ', $final_prod_data ?? ''));
        $Product_Name_temp = explode("|", $Product_Name1);
        $Product_Name = $Product_Name_temp[0];

        $Our_Product_Name1 = trim(preg_replace('/\s+/', ' ', $final_our_product_name ?? ''));
        $Our_Product_Name_temp = explode("|", $Our_Product_Name1);
        $Our_Product_Name = $Our_Product_Name_temp[0];

        $vals['Opening_Stock'] = (isset($vals['Opening_Stock']) && $vals['Opening_Stock'] !== '' && is_numeric($vals['Opening_Stock']))
            ? (float) $vals['Opening_Stock']
            : 0;

        $vals['Stock_Price'] = (isset($vals['Stock_Price']) && $vals['Stock_Price'] !== '' && is_numeric($vals['Stock_Price']))
            ? (float) $vals['Stock_Price']
            : 0;
        $vals['divid']    =  $final_div_id;
        $vals['catid']    =  $final_cat_id;
        // Required field check
        $missing = [];
        // if (empty($final_prod_id)) $missing[] = 'Product Name';
        // else if (empty($Product_Name)) $missing[] = 'Product Name';
        if (empty($vals['Product_UOM'])) $missing[] = 'UOM';
        // if (!isset($vals['Inventory_Type']) || $vals['Inventory_Type'] === '') $missing[] = 'Inventory Type';

        if (!empty($missing)) {
            $action = implode(', ', $missing) . ' is not valid';
            $vals['Product_Name'] = $Product_Name;
            $vals['Our_Product_Name'] = $Our_Product_Name;
            $vals['Product_id'] = $final_prod_id ?? '';
            return [
                'response' => array_merge($vals, [
                    'srno' => $srno,
                    'action' => $action
                ]),
                'response_new' => [
                    'srno' => $srno,
                    'is_verify' => 2,
                    'user_id' => Auth::user()->id,
                    'action' => $action,
                    'data' => json_encode($vals),
                ],
                'prod_name_arr' => $prod_name_arr
            ];
        }

        if (strlen($Product_Name) < 3) {
            $error_code .= 'Product Name should be at least 3 characters, ';
        }
        //dd($final_prod_data);
        // Enrich data if product exists
        if ($Product_Name || $Our_Product_Name) {
            //$prod = $final_prod_data[$Product_Name];

            $vals['Product_Specification'] = substr($this->sanetiz_all_string_data($vals['Product_Specification'], "encode") ?? '', 0, 2900);
            $vals['Product_Size'] = substr($this->sanetiz_all_string_data($vals['Product_Size'], "encode") ?? '', 0, 1450);
            $vals['Brand'] = substr($vals['Brand'] ?? '', 0, 100);
            $vals['Our_Product_Name'] = substr($this->sanetiz_all_string_data($vals['Our_Product_Name'],"encode") ?? '', 0, 100);
            $vals['Inventory_Grouping'] = substr($this->sanetiz_all_string_data($vals['Inventory_Grouping'],"encode") ?? '', 0, 100);
            $vals['Cost_Center'] = substr($this->sanetiz_all_string_data($vals['Cost_Center'],"encode") ?? '', 0, 100);
            $vals['Product_id'] = $final_prod_id ?? '';
            $vals['catid'] = $final_cat_id ?? '';
            $vals['divid'] = $final_div_id ?? '';
            $vals['divname'] = '';
            $vals['catname'] = '';
        }
        //echo '<pre>'; print_r($vals); echo '</pre>';
        $uom = strtolower($vals['Product_UOM']);
        $uom = $this->getUOMIdByName($uom);
        //dd($uom);
        $inventory = strtolower($vals['Inventory_Type'] ?? '');

        $vals['uom'] = $uom ?? 0;
        $vals['Inventory_Type'] = $invt_type_vls[$inventory] ?? 0;

        // UOM check
        if ($uom <= 0) {
            $error_code .= 'UOM is not valid, ';
        }

        // Stock check
        $openingStock = (float) $vals['Opening_Stock'];
        $stockPrice   = (float) $vals['Stock_Price'];

        if (
            ($openingStock > 0 && $stockPrice > 0) ||
            ($openingStock == 0.0 && $stockPrice == 0.0)
        ) {
            // Do nothing
        } else {

            if (
                is_null($openingStock) || is_null($stockPrice)
                // !is_null($openingStock) && !is_null($stockPrice) &&
                // !(($openingStock > 0 && $stockPrice > 0) || ($openingStock == 0.0 && $stockPrice == 0.0))
            ) {
                $error_code .= 'Invalid opening stock or stock price, ';
            }
        }


        $action = rtrim($error_code, ', ');
        $is_verify = $error_code ? 2 : 1;
        if ($final_prod_id == '') {
            $Product_Name_temp = explode("|", $Product_Name);
            $vals['Product_Name'] = $Product_Name_temp[0];
            $vals['Product_id'] = $final_prod_id ?? '';;
            $action = 'Invalid Raprocure Product';
        }else if(!$action){
            $Product_Name_temp = explode("|", $Product_Name);
            $vals['Product_Name'] = $Product_Name_temp[0];
            $vals['Product_id'] = $final_prod_id ?? '';;
            $action = 'Product verified';
        }
        $vals['Product_id'] = $final_prod_id ?? '';;
        // Update unique key tracking
        $prod_name_arr[$Product_Name][$vals['Product_Size'] ?? ''][$vals['Product_Specification'] ?? ''] = $Product_Name;

        return [
            'response' => array_merge($vals, [
                'srno' => $srno,
                'action' => $action,
            ]),
            'response_new' => [
                'srno' => $srno,
                'is_verify' => $is_verify,
                'user_id' => Auth::user()->id,
                'action' => $action,
                'data' => json_encode($vals),
            ],
            'prod_name_arr' => $prod_name_arr
        ];
    }

    private function getUOMIdByName($uom){
        $uomRecord = Uom::where('status', 1)
                    ->where('uom_name', 'like', '%' . $uom . '%')
                    ->select('id')
                    ->first();
        if (!empty($uomRecord)) {
            return $uomRecord->id;
        } else {
            return findUomByUomAlias($uom);
        }
    }
    //-----------------------------------------end upload csv---------------------------------------------------------



    public function verify_product_for_supplier_new($product_name, $return_type = false)//pingki
    {
        if (empty($product_name)) {
            return $return_type ? null : 0;
        }

        $product = Product::whereRaw('LOWER(product_name) = ?', [strtolower(trim($product_name))])->first();

        return $return_type ? $product : ($product ? 1 : 0);
    }


    public function remove_special_chars($str){
        $clean_string = mb_convert_encoding($str, "UTF-8", "ISO-8859-1");
        return str_replace(array('<','>','"',"'",'`','~',"(",")", "/", "&", "…", ":", ";", "�"), '', $clean_string);
    }

    public function deleteRow(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'delete', '1');
            }
            $srno       =   $request->srno;
            $user_id = Auth::user()->id;
            if (!$srno) {
                return response()->json(0);
            }

            $deleted = TempInventoryMgt::where('srno', $srno)
            ->where('user_id', $user_id)
            ->delete();
            return response()->json(['success' => (bool) $deleted]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }

    }

    public function updateRowData_old(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'edit', '1');
            }
            $request = $this->trimAndReturnRequest($request);

            $validated = $request->validate([
                'Product_Name'              => 'required|string|max:255',
                'Product_Specification'     => 'nullable|string|max:255',
                'Product_Size'              => 'nullable|string|max:100',
                'Opening_Stock'             => 'nullable|numeric',
                'Product_UOM'               => 'required|string|max:50',
                'Stock_Price'               => 'nullable|numeric',
                'Brand'                     => 'nullable|string|max:100',
                'Our_Product_Name'          => 'nullable|string|max:255',
                'Inventory_Grouping'        => 'nullable|string|max:255',
                'Cost_Center'               => 'nullable|string|max:255',
                'Inventory_Type'            => 'nullable|string|max:100',
                'Set_Min_Qty_for_Indent'    => 'nullable|numeric',
                'Product_id'                => 'nullable|numeric',
                'catid'                     => 'nullable|numeric',
                'divid'                     => 'nullable|numeric',
                'srno'                      => 'nullable|string',
                'action'                    => 'nullable|string',
                'is_verify'                 => 'required|numeric'
            ]);
            $srno = $request->input('srno');

            $data = [
                'product_name'              => $request->input('Product_Name'),
                'product_specification'     => $request->input('Product_Specification'),
                'product_size'              => $request->input('Product_Size'),
                'opening_stock'             => $request->input('Opening_Stock'),
                'product_uom'               => $request->input('Product_UOM'),
                'stock_price'               => $request->input('Stock_Price'),
                'brand'                     => $request->input('Brand'),
                'our_product_name'          => $request->input('Our_Product_Name'),
                'inventory_grouping'        => $request->input('Inventory_Grouping'),
                'Cost_Center'               => $request->input('Cost_Center'),
                'inventory_type'            => $request->input('Inventory_Type'),
                'set_min_qty_for_indent'    => $request->input('Set_Min_Qty_for_Indent'),
                'product_id'                => $request->input('Product_id'),
                'category_id'               => $request->input('catid'),
                'division_id'               => $request->input('divid'),
                'action'                    => $request->input('action'),
                'is_verified'               => $request->input('is_verify'),
            ];

            $upd                                    =   array();
            $upd['data']                            =   json_encode($data);
            $upd['action']                          =   $request->input('action');
            $upd['srno']                            =   $request->input('srno');
            $upd['is_verify']                       =   $request->input('is_verify');
            // Run the update query
            $update = DB::table('temp_inventory_mgt')->where('srno', $srno)->update($upd);

            if ($update) {
                return response()->json(['status' => 'success', 'message' => 'Inventory row updated successfully']);
            } else {
                return response()->json(['status' => 'fail', 'message' => 'No matching record found or nothing changed']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function updateRowData(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'edit', '1');
            }

            $request = $this->trimAndReturnRequest($request);

            $validated = $request->validate([
                'Product_Name'           => 'nullable|string|max:255',
                'Product_Specification'  => 'nullable|string|max:255',
                'Product_Size'           => 'nullable|string|max:100',
                'Opening_Stock'          => 'nullable|numeric',
                'Product_UOM'            => 'required|string|max:50',
                'Stock_Price'            => 'nullable|numeric',
                'Brand'                  => 'nullable|string|max:100',
                'Our_Product_Name'       => 'nullable|string|max:255',
                'Inventory_Grouping'     => 'nullable|string|max:255',
                'Cost_Center'            => 'nullable|string|max:255',
                'Inventory_Type'         => 'nullable|string|max:100',
                'Set_Min_Qty_for_Indent' => 'nullable|numeric',
                'Product_id'             => 'nullable|numeric',
                'catid'                  => 'nullable|numeric',
                'divid'                  => 'nullable|numeric',
                'srno'                   => 'required|string',
                'action'                 => 'nullable|string',
                'is_verify'              => 'required|numeric'
            ]);

            $srno = $request->input('srno');

            $data = [
                'product_name'           => $request->input('Product_Name'),
                'product_specification'  => $request->input('Product_Specification'),
                'product_size'           => $request->input('Product_Size'),
                'opening_stock'          => $request->input('Opening_Stock') ?? 0,
                'product_uom'            => $request->input('Product_UOM'),
                'stock_price'            => $request->input('Stock_Price') ?? 0,
                'brand'                  => $request->input('Brand'),
                'our_product_name'       => $request->input('Our_Product_Name'),
                'inventory_grouping'     => $request->input('Inventory_Grouping'),
                'Cost_Center'            => $request->input('Cost_Center'),
                'inventory_type'         => $request->input('Inventory_Type'),
                'set_min_qty_for_indent' => $request->input('Set_Min_Qty_for_Indent') ?? 0,
                'product_id'             => $request->input('Product_id') ?? null,
                'category_id'            => $request->input('catid'),
                'division_id'            => $request->input('divid'),
                'action'                 => $request->input('action'),
                'is_verified'            => (int)$request->input('is_verify')
            ];
            // print_r($data);
            // Prepare update data
            $upd = [
                'data'      => json_encode($data, JSON_UNESCAPED_UNICODE),
                'action'    => $request->input('action'),
                'srno'      => $srno,
                'is_verify' => (int)$request->input('is_verify')
            ];

            // Update the row in temp_inventory_mgt
            $update = DB::table('temp_inventory_mgt')->where('srno', $srno)->update($upd);

            if ($update) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Inventory row updated successfully'
                ]);
            } else {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'No matching record found or nothing changed'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function checkBulkInventory(Request $request)
    {
        $buyer_id = Auth::user()->id;
        $query = TempInventoryMgt::select('temp_inventory_mgt.id')
        ->where('temp_inventory_mgt.user_id', $buyer_id)
        ->whereNotNull('temp_inventory_mgt.is_verify');
        $inventoryData = $query->get();

        if (!empty($inventoryData) && count($inventoryData) > 0) {
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }

    public function updateBulkProducts_old(Request $request)
    {
        try {

            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'add', '1');
            }
            $request = $this->trimAndReturnRequest($request);
            $branch_id      =   $request->input('buyer_branch');
            if (session('branch_id') != $request->input('buyer_branch')) {
                    session(['branch_id' => $request->input('buyer_branch')]);
                }
            $company_id     =   (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
            $buyer_id       =   Auth::user()->id;
            $query = TempInventoryMgt::select(
                'temp_inventory_mgt.id',
                'temp_inventory_mgt.is_verify',
                'temp_inventory_mgt.user_id',
                'temp_inventory_mgt.data'
            )
            ->where('temp_inventory_mgt.user_id', $buyer_id);
            $data = $query->get();
            $ins_data       =   array();
            $prokey         =   0;
            if(!empty($data)){
                $ins_data       =   array();
                $prokey         =   0;
                foreach($data as $rws){
                    if($rws->is_verify==1){
                        $decoded    =   $rws->data;
                        if (is_array($decoded) || is_object($decoded)) {
                            $ins_data[$prokey] = (object) $decoded;
                            $ins_data[$prokey]->srno    =   $rws->srno;
                            $prokey++;
                        } elseif (is_string($decoded)) {
                            $ins_data[$prokey] = json_decode($decoded);
                            $ins_data[$prokey]->srno    =   $rws->srno;
                            $prokey++;
                        } else {

                        }
                    }

                }
                $max_inv_id =   0;
                $inventory_unique_id = DB::table('inventories')
                ->where('buyer_parent_id', $company_id)
                ->where('buyer_branch_id', $branch_id)
                ->max('inventory_unique_id');

                if($inventory_unique_id){
                    $max_inv_id =   ($inventory_unique_id);
                }
                $j                  =   1;
                $k                  =   0;
                $ns                 =   0;
                $tot                =   $data->count();
                $inserted           =   array();
                $prod_name_arr      =   array();
                $duplicate_array    =   array();
                $invProducts = DB::table('inventories')
                ->select('product_id', 'specification', 'size')
                ->where('buyer_parent_id', $company_id)
                ->where('buyer_branch_id', $branch_id)
                ->get();

                $prod_name_arr = [];

                if ($invProducts->isNotEmpty()) {
                    foreach ($invProducts as $invProd) {
                        $spec   =   trim(cleanInvisibleCharacters($invProd->specification ?? ''));
                        $size   =   trim(cleanInvisibleCharacters($invProd->size ?? ''));
                        if (!isset($prod_name_arr[$invProd->product_id])) {
                            $prod_name_arr[$invProd->product_id] = [];
                        }
                        if (!isset($prod_name_arr[$invProd->product_id][$spec])) {
                            $prod_name_arr[$invProd->product_id][$spec] = [];
                        }
                        $prod_name_arr[$invProd->product_id][$spec][$size] = true;
                    }
                }
                //echo "<pre>"; print_r($ins_data); die;
                foreach($ins_data as $i => $val){
                    // $csv_product_id             =   $val->Product_id ?? $val->product_id ?? '';
                    $csv_product_id             = isset($val->Product_id) ? (int)$val->Product_id : (isset($val->product_id) ? (int)$val->product_id : null);
                    if (empty($csv_product_id) || $csv_product_id == 0) {
                        $master = DB::table('products')
                            ->where('product_name', trim($val->Product_Name ?? $val->product_name ?? ''))
                            ->first();

                        if ($master) {
                            $csv_product_id = $master->id;
                        } else {
                            $ns++;
                            continue;
                        }
                    }
                    $csv_product_specification  =   $val->Product_Specification ?? $val->product_specification ?? '';
                    $csv_product_size           =   $val->Product_Size ?? $val->product_size ?? '';
                    $csv_opening_stock          =   $val->Opening_Stock ?? $val->opening_stock ?? '';
                    $csv_stock_price            =   $val->Stock_Price ?? $val->stock_price ?? '';
                    $csv_product_uom            =   $val->uom ?? $val->product_uom ?? '';
                    $csv_inventory_grouping     =   $val->inventory_grouping ?? $val->inventory_grouping ?? '';
                    $csv_cost_center            =   $val->cost_center ?? $val->cost_center ?? '';
                    // $csv_inventory_type      =   $val->Inventory_Type ?? $val->inventory_type ?? '';
                    $csv_inventory_type         = isset($val->inventory_type) && $val->inventory_type !== '' ? (int)$val->inventory_type : null;
                    $set_min_qty_for_indent     =   $val->Set_Min_Qty_for_Indent ?? $val->set_min_qty_for_indent ?? '';
                    $set_brand                  =   $val->Brand ?? $val->brand ?? '';

                    // if(!isset($prod_name_arr[$csv_product_id][strtolower($csv_product_specification)][strtolower($csv_product_size)])){
                    $csv_product_specification = trim(cleanInvisibleCharacters($csv_product_specification ?? ''));
                    $csv_product_size = trim(cleanInvisibleCharacters($csv_product_size ?? ''));
                    if($csv_product_uom =="0"){
                        $ns++;
                        continue;
                    }
                    if (empty($prod_name_arr[$csv_product_id][$csv_product_specification][$csv_product_size])) {
                        $inserted[$k]['inventory_unique_id']    =   ($max_inv_id) + $j;
                        $inserted[$k]['buyer_parent_id']        =   $company_id;
                        $inserted[$k]['buyer_branch_id']        =   $branch_id;
                        $inserted[$k]['product_id']             =   $csv_product_id;
                        $inserted[$k]['product_name']           =   $val->Product_Name ?? $val->product_name ?? '';
                        $inserted[$k]['buyer_product_name']     =   $val->Our_Product_Name ?? $val->our_product_name ?? '';
                        $inserted[$k]['specification']          =   substr($csv_product_specification,0,2900);
                        $inserted[$k]['size']                   =   substr($csv_product_size,0,1450);
                        $inserted[$k]['opening_stock']          =   isset($csv_opening_stock) && !empty($csv_opening_stock) && is_numeric($csv_opening_stock) ? $csv_opening_stock : '0';
                        $inserted[$k]['stock_price']            =   isset($csv_stock_price) && !empty($csv_stock_price)? $csv_stock_price : '0';
                        $inserted[$k]['uom_id']                 =   $csv_product_uom;
                        $inserted[$k]['inventory_grouping']     =   $csv_inventory_grouping;
                        $inserted[$k]['cost_center']            =   $csv_cost_center;
                        $inserted[$k]['inventory_type_id']      =   $csv_inventory_type;
                        $inserted[$k]['indent_min_qty']         =   $set_min_qty_for_indent;
                        $inserted[$k]['product_brand']          =   $set_brand;
                        $inserted[$k]['created_by']             =   $buyer_id;
                        $inserted[$k]['updated_by']             =   $buyer_id;
                        $inserted[$k]['created_at']             =   date('Y-m-d H:i:s');
                        $inserted[$k]['updated_at']             =   date('Y-m-d H:i:s');
                        $prod_name_arr[$csv_product_id][strtolower($csv_product_specification)][strtolower($csv_product_size)]= $csv_product_size;
                        $j++;
                        $k++;
                    }
                    else{
                        if($ns<=500){
                            $duplicate_array[$val->srno]    =   $val->srno;
                        }
                        $ns++;
                    }
                }
                $dataInserted   = false;
                dd($inserted); die();
                foreach (array_chunk($inserted, 2000) as $chunk) {
                    $success = DB::table('inventories')->insert($chunk);
                    if (!$success) {
                    } else {
                        $dataInserted   =   true;
                    }
                }
                if($dataInserted){
                    return response()->json([
                        'status'        =>  1,
                        'save'          =>  $k,
                        'not_save'      =>  $ns,
                        'total'         =>  $tot,
                        'duplicate'     =>  $duplicate_array,
                        'message'       =>  $k.' Products uploded </br>'.$ns.' Products already exists in Inventory </br>'.$tot.' Total products upload attempted',
                    ]); die;
                }else{
                    return response()->json([
                        'status'        =>  2,
                        'save'          =>  $k,
                        'not_save'      =>  $ns,
                        'total'         =>  $tot,
                        'duplicate'     =>  $duplicate_array,
                        'message'       =>  $k.' Products uploded </br>'.$ns.' Products already exists in Inventory </br>'.$tot.' Total products upload attempted',
                    ]); die;
                }
            }
            else{
                return response()->json([
                    'status' => 1,
                    'message' => 'No Data Found',
                ]); die;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function updateBulkProducts(Request $request)
    {
        try {

            /* ===================== PERMISSION ===================== */
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'add', '1');
            }

            $request = $this->trimAndReturnRequest($request);
            //dd($request);
            $branch_id  = $request->input('buyer_branch');
            $buyer_id   = Auth::user()->id;
            $company_id = (Auth::user()->parent_id != 0)
                ? Auth::user()->parent_id
                : Auth::user()->id;

            if (session('branch_id') != $branch_id) {
                session(['branch_id' => $branch_id]);
            }

            /* ===================== TEMP DATA ===================== */
            $data = TempInventoryMgt::where('user_id', $buyer_id)->get();
            //dd($data);
            if ($data->isEmpty()) {
                return response()->json([
                    'status'  => 1,
                    'message' => 'No Data Found',
                ]);
            }

            /* ===================== MAX INVENTORY ID ===================== */
            $max_inv_id = DB::table('inventories')
                ->where('buyer_parent_id', $company_id)
                ->where('buyer_branch_id', $branch_id)
                ->max('inventory_unique_id') ?? 0;

            /* ===================== EXISTING INVENTORY MAP ===================== */
            $existingMap = [];

            $existing = DB::table('inventories')
                ->select(
                    'buyer_branch_id',
                    'product_id',
                    'product_name',
                    'buyer_product_name',
                    'specification',
                    'size'
                )
                ->where('buyer_parent_id', $company_id)
                ->where('buyer_branch_id', $branch_id)
                ->get();

            foreach ($existing as $row) {
                $key = implode('|', [
                    $row->buyer_branch_id,
                    $row->product_id,
                    strtolower(trim(cleanInvisibleCharacters($row->product_name ?? ''))),
                    strtolower(trim(cleanInvisibleCharacters($row->buyer_product_name ?? ''))),
                    strtolower(trim(cleanInvisibleCharacters($row->specification ?? ''))),
                    strtolower(trim(cleanInvisibleCharacters($row->size ?? '')))
                ]);
                $existingMap[$key] = true;
            }

            /* ===================== COUNTERS ===================== */
            $inserted        = [];
            $duplicate_array = [];
            $uploadMap       = [];

            $j   = 1;
            $k   = 0;
            $ns  = 0;
            $tot = $data->count();

            /* ===================== MAIN LOOP ===================== */
            $inventoryController = new InventoryController();
            foreach ($data as $row) {

                // if ($row->is_verify != 1) {
                //     continue;
                // }

                $val = is_array($row->data) || is_object($row->data)
                    ? (object)$row->data
                    : json_decode($row->data);
                //dd($val);
                if (!$val) {
                    $ns++;
                    continue;
                }

                /* ---------- PRODUCT ID ---------- */
                $csv_product_id = isset($val->Product_id)
                    ? (int)$val->Product_id
                    : (isset($val->product_id) ? (int)$val->product_id : 0);

                if ($csv_product_id <= 0) {
                    $master = DB::table('products')
                        ->where('product_name', trim($val->Product_Name ?? $val->product_name ?? ''))
                        ->first();

                    if (!$master) {
                        $csv_product_id = 0;
                        //$ns++;
                        //continue;
                    }else{
                        $csv_product_id = $master->id;
                    }

                }
                //dd($val);
                /* ---------- FIELDS ---------- */
                $csv_product_name = trim(cleanInvisibleCharacters(
                    $val->Product_Name ?? $val->product_name ?? ''
                ));

                $csv_our_product_name = trim(cleanInvisibleCharacters(
                    $val->Our_Product_Name ?? $val->Our_Product_Name ?? ''
                ));

                $csv_spec = trim(cleanInvisibleCharacters(
                    $val->Product_Specification ?? $val->product_specification ?? ''
                ));

                $csv_size = trim(cleanInvisibleCharacters(
                    $val->Product_Size ?? $val->product_size ?? ''
                ));

                $csv_uom  = $val->uom ?? $val->product_uom ?? '';

                if ($csv_uom == "0" || $csv_uom === '') {
                    $ns++;
                    continue;
                }

                /* ---------- DUPLICATE KEY ---------- */
                $key = implode('|', [
                    $branch_id,
                    $csv_product_id,
                    strtolower($csv_product_name),
                    strtolower($csv_our_product_name),
                    strtolower($csv_spec),
                    strtolower($csv_size)
                ]);

                // DB duplicate
                if (isset($existingMap[$key])) {
                    if ($ns <= 2000) {
                        $duplicate_array[$row->srno] = $row->srno;
                    }
                    $ns++;
                    continue;
                }

                // Same-upload duplicate
                if (isset($uploadMap[$key])) {
                    if ($ns <= 2000) {
                        $duplicate_array[$row->srno] = $row->srno;
                    }
                    $ns++;
                    continue;
                }

                $uploadMap[$key] = true;
                /* ---------- PREPARE INSERT ---------- */
                
                $itemCode = $inventoryController->generateItemCode($company_id, $branch_id);
                $itemCode = explode('-', $itemCode);
                $itemCode = $itemCode[0] . '-' . $itemCode[1]+$k;
                $inserted[$k] = [
                    'inventory_unique_id' => $max_inv_id + $j,
                    'item_code'           => $itemCode,
                    'buyer_parent_id'     => $company_id,
                    'buyer_branch_id'     => $branch_id,
                    'product_id'          => $csv_product_id,
                    'product_name'        => $csv_product_name,
                    'buyer_product_name'  => $val->Our_Product_Name ?? '',
                    'specification'       => substr($csv_spec, 0, 2900),
                    'size'                => substr($csv_size, 0, 1450),
                    'opening_stock'       => is_numeric($val->Opening_Stock ?? null) ? $val->Opening_Stock : '0',
                    'stock_price'         => is_numeric($val->Stock_Price ?? null) ? $val->Stock_Price : '0',
                    'uom_id'              => $csv_uom,
                    'inventory_grouping'  => $val->inventory_grouping ?? '',
                    'cost_center'         => $val->Cost_Center ?? '',
                    'inventory_type_id'   => isset($val->inventory_type) && $val->inventory_type !== ''
                                                ? (int)$val->inventory_type : null,
                    'indent_min_qty'      => $val->set_min_qty_for_indent ?? '',
                    'product_brand'       => $val->brand ?? '',
                    'created_by'          => $buyer_id,
                    'updated_by'          => $buyer_id,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                $j++;
                $k++;
            }

            /* ===================== INSERT ===================== */
            $dataInserted = false;
            //dd($inserted);
            foreach (array_chunk($inserted, 2000) as $chunk) {
                if (DB::table('inventories')->insert($chunk)) {
                    $dataInserted = true;
                }
            }

            /* ===================== RESPONSE ===================== */
            return response()->json([
                'status'    => $dataInserted ? 1 : 2,
                'save'      => $k,
                'not_save'  => $ns,
                'total'     => $tot,
                'duplicate' => $duplicate_array,
                'message'   =>  $k . ' Products uploded </br>' .
                                $ns . ' Duplicate Products upload attempted </br>' .
                                $tot . ' Total products upload attempted',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function updateBulkProducts_1(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'add', '1');
            }

            $request = $this->trimAndReturnRequest($request);
            $branch_id = $request->input('buyer_branch');
            $buyer_id = Auth::user()->id;

            session(['branch_id' => $branch_id]);

            $tempRows = TempInventoryMgt::select('id', 'srno', 'is_verify', 'data')
                ->where('user_id', $buyer_id)
                ->get();

            if ($tempRows->isEmpty()) {
                return response()->json([
                    'status' => 1,
                    'message' => 'No Data Found',
                ]);
            }

            $ins_data = [];
            foreach ($tempRows as $row) {
                if ($row->is_verify != 1) continue;

                $decoded = json_decode($row->data);
                if (!$decoded) continue;

                $decoded->srno = $row->srno;
                $ins_data[] = $decoded;
            }

            $existing = DB::table('inventories')
                ->select('product_id', 'specification', 'size')
                ->where('buyer_parent_id', $buyer_id)
                ->where('buyer_branch_id', $branch_id)
                ->get();

            $prod_name_arr = [];
            foreach ($existing as $p) {
                $spec = strtolower(trim($p->specification ?? ''));
                $size = strtolower(trim($p->size ?? ''));
                $prod_name_arr[$p->product_id][$spec][$size] = true;
            }

            $max_inv_id = DB::table('inventories')
                ->where('buyer_parent_id', $buyer_id)
                ->where('buyer_branch_id', $branch_id)
                ->max('inventory_unique_id') ?? 0;

            $insertData = [];
            $duplicate = [];
            $saved = 0;
            $not_saved = 0;
            $counter = 1;

            foreach ($ins_data as $val) {
                $csv_product_id = isset($val->Product_id)
                    ? (int)$val->Product_id
                    : (isset($val->product_id) ? (int)$val->product_id : null);

                if (empty($csv_product_id) || $csv_product_id == 0) {
                    $master = DB::table('products')
                        ->where('product_name', trim($val->Product_Name ?? $val->product_name ?? ''))
                        ->first();

                    if ($master) {
                        $csv_product_id = $master->id;
                    } else {
                        $not_saved++;
                        continue;
                    }
                }

                $spec = strtolower(trim($val->Product_Specification ?? $val->product_specification ?? ''));
                $size = strtolower(trim($val->Product_Size ?? $val->product_size ?? ''));

                if (!empty($prod_name_arr[$csv_product_id][$spec][$size])) {
                    if ($not_saved <= 500) $duplicate[] = $val->srno;
                    $not_saved++;
                    continue;
                }
                if($val->uom == ''){
                    $not_saved++;
                    continue;
                }

                $insertData[] = [
                    'inventory_unique_id' => $max_inv_id + $counter,
                    'buyer_parent_id' => $buyer_id,
                    'buyer_branch_id' => $branch_id,
                    'product_id' => $csv_product_id,
                    'product_name' => $val->Product_Name ?? $val->product_name ?? '',
                    'buyer_product_name' => $val->Our_Product_Name ?? $val->our_product_name ?? '',
                    'specification' => substr($spec, 0, 2900),
                    'size' => substr($size, 0, 1450),
                    'opening_stock' => is_numeric($val->Opening_Stock ?? $val->opening_stock ?? '') ? ($val->Opening_Stock ?? $val->opening_stock) : 0,
                    'stock_price' => is_numeric($val->Stock_Price ?? $val->stock_price ?? '') ? ($val->Stock_Price ?? $val->stock_price) : 0,
                    'uom_id' => $val->uom ?? $val->product_uom ?? '',
                    'inventory_grouping' => $val->Inventory_Grouping ?? '',
                    'cost_center' => $val->cost_center ?? '',
                    'inventory_type_id' => isset($val->Inventory_Type) ? (int)$val->Inventory_Type : null,
                    'indent_min_qty' => $val->Set_Min_Qty_for_Indent ?? '',
                    'product_brand' => $val->Brand ?? '',
                    'created_by' => $buyer_id,
                    'updated_by' => $buyer_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $prod_name_arr[$csv_product_id][$spec][$size] = true;
                $saved++;
                $counter++;
            }
            dd($insertData);
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 2000) as $chunk) {
                    DB::table('inventories')->insert($chunk);
                }
            }

            return response()->json([
                'status' => 1,
                'save' => $saved,
                'not_save' => $not_saved,
                'total' => count($ins_data),
                'duplicate' => $duplicate,
                'message' => "$saved Products uploaded<br>$not_saved Products already exist or invalid<br>" . count($ins_data) . " Total processed",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function sanetiz_all_string_data($vals, $type="")
    {
        if($type=="decode"){
            $vals = str_replace(array('&#60;'), '<', $vals);
            $vals = str_replace(array('&#62;'), '>', $vals);
            $vals = str_replace(array('&#34;'), '"', $vals);
            $vals = str_replace(array('&#39;'), "'", $vals);
            $vals = str_replace(array('&#96;'), "`", $vals);
            $vals = str_replace(array('&#126;'), "~", $vals);
        }else if($type=="encode"){
            $vals = str_replace(array('<'), '&#60;', $vals);
            $vals = str_replace(array('>'), '&#62;', $vals);
            $vals = str_replace(array('"'), '&#34;', $vals);
            $vals = str_replace(array("'"), '&#39;', $vals);
            $vals = str_replace(array("`"), '&#96;', $vals);
            $vals = str_replace(array("~"), '&#126;', $vals);
        }
        return $vals;
    }
}
