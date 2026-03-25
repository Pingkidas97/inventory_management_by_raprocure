<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class LiveVendorProduct extends Model
{
    protected $table = 'view_live_vend_with_alias';
    public $incrementing = false;
    public $timestamps = false;

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public static function getCategoryProduct($request)
    {
        $category_id = $request->category_id;
        $product_name = $request->product_name;
        $sort_by = $request->sort_by;

        $query = self::where('category_id', $category_id)
                    ->select('product_id', 'product_name')
                    ->distinct();
        
        if(!empty($product_name)){
            $words = preg_split('/\s+/', $product_name, -1, PREG_SPLIT_NO_EMPTY); // Split by space(s)

            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where('product_name', 'like', '%' . $word . '%');
                }
            });
        }
        if(!empty($sort_by)){
            if($sort_by == 1){
                $sort_by_product = 'asc';
            }else{
                $sort_by_product = 'desc';
            }
            $query->orderBy('product_name', $sort_by_product);
        }
        $category = $query->get();
        
        
        return $category;
    }
    
    public static function getSearchedProduct($search_arr)
    {
        $product_name = $search_arr['product_name'];
        $per_page = $search_arr['per_page'];
        $page = $search_arr['page'];
        $draft_products = $search_arr['draft_products'];

        $search_key_arr = preg_split('/\s+/', $product_name, -1, PREG_SPLIT_NO_EMPTY); 

        $query = "SELECT product_id,product_name,division_id,category_id from view_live_vend_with_alias WHERE product_id > 0 ";

        $p_name = $tag = '';
        $search_for = " AND ";
        foreach ($search_key_arr as $key => $srchrow) {
            if (!empty($p_name)) {
                $p_name.= $search_for;
                $tag.= $search_for;
            }
            $p_name.= " product_name like '%$srchrow%' ";
            $tag.= " alias like '%$srchrow%' ";
        }
        $query .= " AND ( ";
        $query .= " ( " . $p_name . " )" . " OR " . "( " . $tag . " ) ";
        $query .= " ) ";

        if (isset($draft_products) && !empty($draft_products)) {
            $query .= " AND product_id NOT IN('" . implode("','", $draft_products) . "') ";
        }
        $query .= " GROUP BY product_id,product_name,division_id,category_id ";

        $results = DB::table(DB::raw("({$query}) as sub"))
                ->select('sub.*', 'divisions.division_name', 'categories.category_name')
                ->join('divisions', 'sub.division_id', '=', 'divisions.id')
                ->join('categories', 'sub.category_id', '=', 'categories.id')
                ->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->get();
                
        return $results;
    }

    public static function getSuggesationSearchedProduct($search_arr)
    {
        $product_name = $search_arr['product_name'];
        $per_page = $search_arr['per_page'];
        $page = $search_arr['page'];
        $draft_products = $search_arr['draft_products'];
        $ESCAPE_SEARCH = array('a', 'an', 'the', 'is', 'and', 'are', 'with');
        $sql_query = '';
        $where2 = '';
        $where3 = '';

        $product_name = self::cleanString($product_name);
        
        $keywords_array = [];
        $words_array = preg_split('/\s+/', $product_name, -1, PREG_SPLIT_NO_EMPTY); // Split by space(s)
        foreach ($words_array as $key => $row) {
            if (!in_array(strtolower($row), $ESCAPE_SEARCH)) {
                $keywords_array[] = $row;
            }
        }

        if (isset($draft_products) && !empty($draft_products)) {
            $where3 = " AND product_id NOT IN('" . implode("','", $draft_products) . "') ";
        }
        
        if (count($keywords_array) > 1) {
            $combinations = generateCombinations($keywords_array, 4);//search suggesation only for first 4 words
            $new_array = array();
            foreach ($combinations as $combination) {
                if (count($combination) > 1) {
                    $new_array[count($combination) ][] = $combination;
                }
            }
            $new_array = array_reverse($new_array);
            $all_sql = array();
            foreach ($new_array as $comb) {
                $sql1 = array();
                foreach ($comb as $words) {
                    $sql1[] = "( product_name like '%" . implode("%' and product_name like '%", $words) . "%' ) OR ( alias like '%" . implode("%' and alias like '%", $words) . "%' ) ";
                }
                $all_sql[] = "SELECT product_id,product_name,division_id,category_id FROM view_live_vend_with_alias WHERE (" . implode(" OR ", $sql1) . ") " . $where3 . " GROUP by product_id,product_name,division_id,category_id";
            }
            unset($all_sql[0]);
            if (!empty($all_sql)) {
                $sql_query = implode(" union distinct ", $all_sql);
                $sql_query.= ' union distinct ';
                $sql_query.= ' select "", "Suggestions below", "", "" union distinct ';
            }
        }

        $p_name = $tag = '';
        $search_for = " OR ";
        $trimmedArray = array_slice($keywords_array, 0, 4, true);//search suggesation only for first 4 words
        foreach ($trimmedArray as $key => $srchrow) {
            if (!empty($p_name)) {
                $p_name.= $search_for;
                $tag.= $search_for;
            }
            $p_name.= " product_name like '%" . $srchrow . "%' ";
            $tag.= " alias like '%" . $srchrow . "%' ";
        }
        $where2 = " ( ";
        $where2.= " ( " . $p_name . " )" . " OR " . "( " . $tag . " )";
        $where2.= " ) ";
        $sql_query.= " SELECT product_id,product_name,division_id,category_id FROM view_live_vend_with_alias WHERE  " . $where2 . $where3 . " GROUP BY product_id,product_name,division_id,category_id ";

        $results = DB::table(DB::raw("({$sql_query}) as sub"))
                ->select('sub.*', 'divisions.division_name', 'categories.category_name')
                ->join('divisions', 'sub.division_id', '=', 'divisions.id')
                ->join('categories', 'sub.category_id', '=', 'categories.id')
                ->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->get();

        // Convert to array for merging if needed
        // $resultsArray = $results->toArray();

        // if ($page == 1) {
        //     $searchKey = $search_arr['product_name'] ?? '';
        //     $showing_result = 'Showing result for "' . $searchKey . '" 0 record found';

        //     $first_index = [
        //         (object)[
        //             "product_id" => "",
        //             "product_name" => $showing_result,
        //             "division_id" => "",
        //             "category_id" => "",
        //             "division_name" => "",
        //             "category_name" => "",
        //         ]
        //     ];

        //     $second_index = [
        //         (object)[
        //             "product_id" => "",
        //             "product_name" => "Suggestions are:",
        //             "division_id" => "",
        //             "category_id" => "",
        //             "division_name" => "",
        //             "category_name" => "",
        //         ]
        //     ];

        //     // Merge all as collection again
        //     $results = collect(array_merge($first_index, $second_index, $resultsArray));
        // }
                
        return $results;
    }

    public static function cleanString($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z\-]/', '', $string); // Removes special chars.//0-9
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return str_replace('-', ' ', $string); // Replaces all hyphens with spaces.
    }
}
