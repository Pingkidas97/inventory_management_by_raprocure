<?php
namespace App\Http\Controllers\Buyer;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Buyer\SearchProductController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class InventoryVendorProductController extends Controller
{
    public function search_guru(Request $request)
    {

        $SearchProductController = app(SearchProductController::class);
        $searchResult = $SearchProductController->searchVendorActiveProductForInv($request);
        return response()->json($searchResult);

    }
    public function search(Request $request)
    {
        $query  = trim($request->query('query', ''));
        $page   = max((int)$request->query('page', 1), 1);
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = "product_search_{$query}_page_{$page}";

        $result = Cache::remember($cacheKey, 60, function () use ($query, $limit, $offset) {

            $query = $this->sanitize($query);

            $baseQuery = clone $this->searchQuery($query);

            return $baseQuery
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id'            => $product->id,
                        'product_name'  => $product->product_name,
                        'category_id'   => $product->category_id,
                        'division_id'   => $product->division_id,
                        'category_name' => optional($product->category)->category_name,
                        'division_name' => optional($product->division)->division_name,
                    ];
                });
        });

        
        return response()->json($result);
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
    public function searchAllProduct_guru(Request $request)
    {
        $searchData = $request->input('search_data');
        $searchType = $request->input('search_type');
        $pageNo     = $request->input('page_no', 1);

        // Validate input
        if (empty(trim($searchData))) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Empty search data',
            ]);
        }

        // Fetch matching products with relationships
        // $products = Product::with(['category', 'division'])
        //     ->where(function ($query) use ($searchData) {
        //         $query->where('product_name', 'like', '%' . $searchData . '%')
        //             ->orWhereHas('category', function ($q) use ($searchData) {
        //                 $q->where('category_name', 'like', '%' . $searchData . '%');
        //             })
        //             ->orWhereHas('division', function ($q) use ($searchData) {
        //                 $q->where('division_name', 'like', '%' . $searchData . '%');
        //             });
        //     })
        //     ->limit(200)
        //     ->get();

        $SearchProductController = app(SearchProductController::class);
        $request->merge(['query' => $searchData, 'page' => $pageNo]);
        $products = $SearchProductController->searchVendorActiveProductForInv($request);

        // No data found
        if ($products->isEmpty()) {
            return response()->json([
                'status'        => 'nodata',
                'message'       => 'No products found',
                'search_result' => '',
                'totalRecords'  => 0
            ]);
        }

        // Format product data
        $formattedProducts = $products->map(function ($product) {
            return [
                'prod_id'   => $product['id'],
                'prod_name' => $product['product_name'],
                'cat_id'    => $product['category_id'],
                'div_id'    => $product['division_id'],
                'cat_name'  => $product['category_name'],
                'div_name'  => $product['division_name'],
            ];
        })->values();

        return response()->json([
            'status'        => 'pass',
            'search_result' => $formattedProducts,
            'totalRecords'  => $products->count(),
            'data'          => $formattedProducts,
            'divisions'     => $products->pluck('division_name')->filter()->unique()->values(),
            'category'      => $products->pluck('category_name')->filter()->unique()->values(),
        ]);
    }
    public function searchAllProduct(Request $request)
    {
        $searchData = trim($request->input('search_data'));

        if ($searchData === '') {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Empty search data',
            ]);
        }

        $words = preg_split('/\s+/', sanitizePNameForBulkProduct($searchData));

        $products = Product::with(['category', 'division'])
            ->where('status', '1')
            ->where(function ($query) use ($words) {

                foreach ($words as $word) {
                    if ($word === '') continue;

                    $query->where(function ($q) use ($word) {
                        $q->where('product_name', 'LIKE', "%{$word}%")
                        ->orWhereHas('product_aliases', function ($alias) use ($word) {
                            $alias->where('alias', 'LIKE', "%{$word}%");
                        })
                        ->orWhereHas('category', function ($cat) use ($word) {
                            $cat->where('category_name', 'LIKE', "%{$word}%");
                        })
                        ->orWhereHas('division', function ($div) use ($word) {
                            $div->where('division_name', 'LIKE', "%{$word}%");
                        });
                    });
                }

            })
            ->orderBy('product_name', 'asc')
            ->limit(200)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status'        => 'nodata',
                'message'       => 'No products found',
                'search_result' => [],
                'totalRecords'  => 0
            ]);
        }

        $formattedProducts = $products->map(function ($product) {
            return [
                'prod_id'   => $product->id,
                'prod_name' => $product->product_name,
                'cat_id'    => $product->category_id,
                'div_id'    => $product->division_id,
                'cat_name'  => optional($product->category)->category_name,
                'div_name'  => optional($product->division)->division_name,
            ];
        })->values();

        return response()->json([
            'status'        => 'pass',
            'search_result' => $formattedProducts,
            'totalRecords'  => $products->count(),
            'data'          => $formattedProducts,
            'divisions'     => $products->pluck('division.division_name')->filter()->unique()->values(),
            'category'      => $products->pluck('category.category_name')->filter()->unique()->values(),
        ]);
    }
}

