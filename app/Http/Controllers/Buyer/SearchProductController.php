<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchProductController extends Controller
{
    public function searchVendorActiveProduct(Request $request)
    {
        $productNameInput = (string) $request->input('product_name', '');
        if (is_numeric($productNameInput)) {
            return response()->json([
                'status' => false,
                'message' => 'No product found',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'yes',
            ]);
        }

        $productName = trim($productNameInput);
        $source = (string) $request->input('source', 'search');

        if ($productName === '') {
            return response()->json([
                'status' => false,
                'message' => 'Please enter product name',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'no',
            ]);
        }

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $limit = 15;
        $offset = $limit * ($page - 1);

        $draftProductIds = [];
        if ($source === 'rfq') {
            $rfqId = $request->input('rfq_id');
            if (!empty($rfqId)) {
                $draftProductIds = DB::table('rfq_products')
                    ->where('rfq_id', $rfqId)
                    ->pluck('product_id')
                    ->filter()
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        $searchKey = strtolower($productName);
        $searchKey = $this->cleanString($searchKey);
        $searchWords = array_values(array_filter(explode(' ', $searchKey)));

        if (empty($searchWords)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid search keyword found',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'no',
            ]);
        }

        $isSuggesation = $request->input('is_suggesation', 'no') === 'yes' ? 'yes' : 'no';
        $filteredTotalCount = (int) $request->input('filtered_total_count', 0);

        $exactProducts = collect();
        $suggestionProducts = collect();
        $showSuggestions = false;

        \Log::info('Search Start', [
            'query' => $productName,
            'page' => $page,
            'isSuggesation' => $isSuggesation
        ]);

        if ($isSuggesation === 'no') {
            $searchResult = $this->runKeywordSearch($searchWords, $draftProductIds, $limit, $offset, $filteredTotalCount);
            $filteredTotalCount = $searchResult['total'];
            $exactProducts = $searchResult['data'];

            \Log::info('Exact Search Result', [
                'exactCount' => $exactProducts->count(),
                'exactTotal' => $filteredTotalCount,
                'page' => $page
            ]);

            if ($exactProducts->isEmpty()) {
                $isSuggesation = 'yes';
                $filteredTotalCount = 0;
                \Log::info('No exact matches - switching to suggestion mode');
            } elseif ($page === 1 && $exactProducts->count() <= 3) {
                // Show suggestions below exact matches if only 1-3 exact products found
                $showSuggestions = true;
                \Log::info('Triggering suggestions', [
                    'reason' => 'page 1 with 1-3 exact matches',
                    'exactCount' => $exactProducts->count()
                ]);
            } else {
                \Log::info('NOT triggering suggestions', [
                    'page' => $page,
                    'exactCount' => $exactProducts->count(),
                    'reason' => $page !== 1 ? 'not page 1' : 'more than 3 exact matches'
                ]);
            }
        }

        if ($isSuggesation === 'yes' || $showSuggestions) {
            // When showing suggestions below exact matches, fetch from start with more results
            $suggestionOffset = $showSuggestions ? 0 : $offset;
            $suggestionLimit = $showSuggestions ? ($limit * 2) : $limit; // Get more suggestions
            
            // Exclude exact match products from suggestion search
            $excludeProductIds = $showSuggestions ? $exactProducts->pluck('product_id')->toArray() : [];
            $combinedDraftIds = array_merge($draftProductIds, $excludeProductIds);
            
            $suggestionResult = $this->runSuggestionSearch($searchWords, $combinedDraftIds, $suggestionLimit, $suggestionOffset);
            $suggestionProducts = $suggestionResult['data'];
            
            // Debug logging
            \Log::info('Suggestion Search Debug', [
                'query' => $productName,
                'showSuggestions' => $showSuggestions,
                'exactCount' => $exactProducts->count(),
                'excludedIds' => $excludeProductIds,
                'suggestionCount' => $suggestionProducts->count(),
                'suggestionTotal' => $suggestionResult['total'],
                'suggestionLimit' => $suggestionLimit,
                'suggestionOffset' => $suggestionOffset
            ]);
            
            if ($isSuggesation === 'yes') {
                // No exact matches - only suggestions
                $filteredTotalCount = $suggestionResult['total'];
            }
        }

        $productHtml = view('buyer.vendor-product.search-product-item', [
            'exact_products' => $exactProducts,
            'suggestion_products' => $suggestionProducts,
            'page' => $page,
            'is_suggesation' => $isSuggesation,
            'show_suggestions' => $showSuggestions,
            'product_name' => $productName,
            'source' => $source,
        ])->render();

        $allProducts = $exactProducts->concat($suggestionProducts)->unique('product_id')->values();

        return response()->json([
            'status' => true,
            'message' => 'Product search completed',
            'is_products' => $allProducts->isNotEmpty(),
            'product_html' => $productHtml,
            'is_suggesation' => $isSuggesation,
            'filtered_total_count' => $filteredTotalCount,
        ]);
    }

    public function searchVendorActiveProductForInv($request)
    {
        $productNameInput = (string) $request->input('query', '');
        if (is_numeric($productNameInput)) {
            return response()->json([
                'status' => false,
                'message' => 'No product found',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'yes',
            ]);
        }

        $productName = trim($productNameInput);
        $source = (string) $request->input('source', 'search');

        if ($productName === '') {
            return response()->json([
                'status' => false,
                'message' => 'Please enter product name',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'no',
            ]);
        }

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $limit = 500;
        $offset = $limit * ($page - 1);

        $draftProductIds = [];
        if ($source === 'rfq') {
            $rfqId = $request->input('rfq_id');
            if (!empty($rfqId)) {
                $draftProductIds = DB::table('rfq_products')
                    ->where('rfq_id', $rfqId)
                    ->pluck('product_id')
                    ->filter()
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        $searchKey = strtolower($productName);
        $searchKey = $this->cleanString($searchKey);
        $searchWords = array_values(array_filter(explode(' ', $searchKey)));

        if (empty($searchWords)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid search keyword found',
                'is_products' => false,
                'product_html' => '',
                'is_suggesation' => 'no',
            ]);
        }

        $isSuggesation = $request->input('is_suggesation', 'no') === 'yes' ? 'yes' : 'no';
        $filteredTotalCount = (int) $request->input('filtered_total_count', 0);

        $products = collect();

        if ($isSuggesation === 'no') {
            $searchResult = $this->runKeywordSearch($searchWords, $draftProductIds, $limit, $offset, $filteredTotalCount);
            $filteredTotalCount = $searchResult['total'];
            $products = $searchResult['data'];

            if ($products->isEmpty()) {
                $isSuggesation = 'yes';
                $filteredTotalCount = 0;
            }
        }

        if ($isSuggesation === 'yes') {
            $suggestionResult = $this->runSuggestionSearch($searchWords, $draftProductIds, $limit, $offset);
            $filteredTotalCount = $suggestionResult['total'];
            $products = $suggestionResult['data'];
        }

        $products = $products->unique('product_id')->values();
        $products = $products->map(function ($product) {
            return [
                'id'            => $product->product_id,
                'product_name'  => $product->product_name,
                'category_id'   => $product->category_id,
                'division_id'   => $product->division_id,
                'category_name' => $product->category_name,
                'division_name' => $product->division_name,
            ];
        });
        return $products;
    }

    private function runKeywordSearch(array $searchWords, array $draftProductIds, int $limit, int $offset, int $knownTotal = 0): array
    {
        return $this->runSearchQuery($searchWords, $draftProductIds, $limit, $offset, true, $knownTotal);
    }

    private function runSuggestionSearch(array $searchWords, array $draftProductIds, int $limit, int $offset): array
    {
        $escapeWords = ['a', 'an', 'the', 'is', 'and', 'are', 'with'];
        $keywords = array_values(array_filter($searchWords, function ($word) use ($escapeWords) {
            return !in_array(strtolower($word), $escapeWords, true);
        }));

        return $this->runSearchQuery($keywords, $draftProductIds, $limit, $offset, false);
    }

    private function runSearchQuery(array $searchWords, array $draftProductIds, int $limit, int $offset, bool $requireAllWords, int $knownTotal = 0): array
    {
        $normalizedWords = $this->normalizeSearchWords($searchWords);
        if (empty($normalizedWords)) {
            return ['total' => 0, 'data' => collect()];
        }

        $booleanTerm = $this->buildBooleanSearchTerm($normalizedWords, $requireAllWords);

        $candidateProductIds = $this->collectCandidateProductIds($normalizedWords, $booleanTerm, $requireAllWords);
        if (empty($candidateProductIds)) {
            return ['total' => 0, 'data' => collect()];
        }

        $eligible = $this->fetchEligibleProductIds($candidateProductIds, $draftProductIds, $limit, $offset, $knownTotal);
        if ($eligible['total'] <= 0 || empty($eligible['ids'])) {
            return ['total' => 0, 'data' => collect()];
        }

        $records = $this->hydrateProductsByIds($eligible['ids']);

        return [
            'total' => $eligible['total'],
            'data' => collect($records),
        ];
    }

    private function normalizeSearchWords(array $searchWords): array
    {
        $normalized = [];
        foreach ($searchWords as $word) {
            $trimmed = trim($word);
            if ($trimmed === '') {
                continue;
            }

            $cleaned = preg_replace('/[^\pL\pN]+/u', ' ', $trimmed);
            if ($cleaned === null) {
                continue;
            }

            $normalizedWord = mb_strtolower(trim($cleaned));
            if ($normalizedWord !== '') {
                $normalized[] = $normalizedWord;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function buildBooleanSearchTerm(array $searchWords, bool $requireAllWords): string
    {
        $terms = [];
        foreach ($searchWords as $word) {
            $clean = preg_replace('/[^\pL\pN]+/u', '', $word);
            if ($clean === null || $clean === '') {
                continue;
            }

            $prefix = $requireAllWords ? '+' : '';
            $terms[] = $prefix . $clean . '*';
        }

        return implode(' ', $terms);
    }

    private function collectCandidateProductIds(array $searchWords, string $booleanTerm, bool $requireAllWords): array
    {
        // $candidates = collect();

        // if ($booleanTerm !== '') {
        //     $candidates = $candidates
        //         ->merge($this->searchProductIdsWithFullText($booleanTerm))
        //         ->merge($this->searchVendorAliasProductIdsWithFullText($booleanTerm))
        //         ->merge($this->searchGenericAliasProductIdsWithFullText($booleanTerm));
        // }

        // // if ($booleanTerm === '' || $this->containsShortWord($searchWords)) {
        // if ($booleanTerm === '' || $this->containsShortWord($searchWords) || $candidates->isEmpty()) {
        //     $candidates = $candidates
        //         ->merge($this->searchProductIdsWithLike($searchWords, $requireAllWords))
        //         ->merge($this->searchVendorAliasProductIdsWithLike($searchWords, $requireAllWords))
        //         ->merge($this->searchGenericAliasProductIdsWithLike($searchWords, $requireAllWords));
        // }

        // return $candidates->unique()->values()->all();


        $fullTextCandidates = collect();

        if ($booleanTerm !== '') {
            $fullTextCandidates = $fullTextCandidates
                ->merge($this->searchProductIdsWithFullText($booleanTerm))
                ->merge($this->searchVendorAliasProductIdsWithFullText($booleanTerm))
                ->merge($this->searchGenericAliasProductIdsWithFullText($booleanTerm));
        }

        $likeCandidates = collect()
            ->merge($this->searchProductIdsWithLike($searchWords, $requireAllWords));

        if ($booleanTerm === '' || $this->containsShortWord($searchWords) || $fullTextCandidates->isEmpty()) {
            $likeCandidates = $likeCandidates
                ->merge($this->searchVendorAliasProductIdsWithLike($searchWords, $requireAllWords))
                ->merge($this->searchGenericAliasProductIdsWithLike($searchWords, $requireAllWords));
        }

        return $fullTextCandidates
            ->merge($likeCandidates)
            ->unique()
            ->values()
            ->all();
    }

    private function fetchEligibleProductIds(array $candidateProductIds, array $draftProductIds, int $limit, int $offset, int $knownTotal = 0): array
    {
        if (empty($candidateProductIds)) {
            return ['total' => 0, 'ids' => []];
        }

        $eligibleQuery = DB::table('products as p')
            ->whereIn('p.id', $candidateProductIds)
            ->where('p.status', 1)
            ->when(!empty($draftProductIds), function ($query) use ($draftProductIds) {
                $query->whereNotIn('p.id', $draftProductIds);
            })
            ->whereExists(function ($exists) {
                $exists->select(DB::raw(1))
                    ->from('vendor_products as vp')
                    ->whereColumn('vp.product_id', 'p.id')
                    ->where('vp.vendor_status', 1)
                    ->where('vp.edit_status', 0)
                    ->where('vp.approval_status', 1);
            });

        $actualTotal = (clone $eligibleQuery)->count();
        $total = $knownTotal > 0 ? min($knownTotal, $actualTotal) : $actualTotal;
        if ($total <= 0) {
            return ['total' => 0, 'ids' => []];
        }

        $ids = (clone $eligibleQuery)
            // ->orderByDesc('p.updated_at')
            // ->orderBy('p.product_name')
            ->offset($offset)
            ->limit($limit)
            ->pluck('p.id')
            ->all();

        return ['total' => $total, 'ids' => $ids];
    }

    private function hydrateProductsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $details = DB::table('products as p')
            ->leftJoin('divisions as d', function ($join) {
                $join->on('p.division_id', '=', 'd.id')
                    ->where('d.status', 1);
            })
            ->leftJoin('categories as c', function ($join) {
                $join->on('p.category_id', '=', 'c.id')
                    ->where('c.status', 1);
            })
            ->whereIn('p.id', $ids)
            ->select(
                'p.id as product_id',
                'p.product_name',
                'p.division_id',
                'p.category_id',
                'p.updated_at as product_updated_at',
                'd.division_name',
                'c.category_name'
            )
            ->get()
            ->keyBy('product_id');

        $ordered = [];
        foreach ($ids as $productId) {
            if (isset($details[$productId])) {
                $ordered[] = $details[$productId];
            }
        }

        return $ordered;
    }

    private function searchProductIdsWithFullText(string $booleanTerm): array
    {
        return DB::table('products as p')
            ->where('p.status', 1)
            ->whereRaw('MATCH(p.product_name) AGAINST (? IN BOOLEAN MODE)', [$booleanTerm])
            ->pluck('p.id')
            ->all();
    }

    private function searchVendorAliasProductIdsWithFullText(string $booleanTerm): array
    {
        return DB::table('vendor_products as vp')
            ->join('products as p', 'p.id', '=', 'vp.product_id')
            ->join('product_alias as pav', function ($join) {
                $join->on('pav.product_id', '=', 'vp.product_id')
                    ->on('pav.vendor_id', '=', 'vp.vendor_id');
            })
            ->where('p.status', 1)
            ->where('vp.vendor_status', 1)
            ->where('vp.approval_status', 1)
            ->where('vp.edit_status', 0)
            ->whereRaw('MATCH(pav.alias) AGAINST (? IN BOOLEAN MODE)', [$booleanTerm])
            ->distinct()
            ->pluck('p.id')
            ->all();
    }

    private function searchGenericAliasProductIdsWithFullText(string $booleanTerm): array
    {
        return DB::table('product_alias as pag')
            ->join('products as p', 'p.id', '=', 'pag.product_id')
            ->whereNull('pag.vendor_id')
            ->where('p.status', 1)
            ->whereRaw('MATCH(pag.alias) AGAINST (? IN BOOLEAN MODE)', [$booleanTerm])
            ->pluck('p.id')
            ->all();
    }

    private function searchProductIdsWithLike(array $words, bool $requireAllWords): array
    {
        if (empty($words)) {
            return [];
        }

        $query = DB::table('products as p')->where('p.status', 1);

        $this->applyWordConditions($query, 'p.product_name', $words, $requireAllWords);

        return $query->distinct()->pluck('p.id')->all();
    }

    private function searchVendorAliasProductIdsWithLike(array $words, bool $requireAllWords): array
    {
        if (empty($words)) {
            return [];
        }

        $query = DB::table('vendor_products as vp')
            ->join('products as p', 'p.id', '=', 'vp.product_id')
            ->join('product_alias as pav', function ($join) {
                $join->on('pav.product_id', '=', 'vp.product_id')
                    ->on('pav.vendor_id', '=', 'vp.vendor_id');
            })
            ->where('p.status', 1)
            ->where('vp.vendor_status', 1)
            ->where('vp.edit_status', 0)
            ->where('vp.approval_status', 1);

        $this->applyWordConditions($query, 'pav.alias', $words, $requireAllWords);

        return $query->distinct()->pluck('p.id')->all();
    }

    private function searchGenericAliasProductIdsWithLike(array $words, bool $requireAllWords): array
    {
        if (empty($words)) {
            return [];
        }

        $query = DB::table('product_alias as pag')
            ->join('products as p', 'p.id', '=', 'pag.product_id')
            ->whereNull('pag.vendor_id')
            ->where('p.status', 1);

        $this->applyWordConditions($query, 'pag.alias', $words, $requireAllWords);

        return $query->distinct()->pluck('p.id')->all();
    }

    private function applyWordConditions(Builder $query, string $column, array $words, bool $requireAllWords): void
    {
        if ($requireAllWords) {
            foreach ($words as $word) {
                $query->where($column, 'like', $this->wrapLikeWildcard($word));
            }

            return;
        }

        $query->where(function ($orGroup) use ($column, $words) {
            foreach ($words as $word) {
                $orGroup->orWhere($column, 'like', $this->wrapLikeWildcard($word));
            }
        });
    }

    private function wrapLikeWildcard(string $word): string
    {
        $escaped = addcslashes($word, '\\%_');

        return '%' . $escaped . '%';
    }

    private function containsShortWord(array $searchWords): bool
    {
        foreach ($searchWords as $word) {
            if (mb_strlen($word) < 2) {
                return true;
            }
        }

        return false;
    }

    private function cleanString(string $string): string
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z\-]/', '', $string);
        $string = preg_replace('/-+/', '-', $string);

        return str_replace('-', ' ', $string);
    }

    public function getSearchByDivision(Request $request)
    {
        $divisionsHtml = Cache::remember('buyer_search_by_division_html', 600, function () {
            $activeVendorProducts = DB::table('vendor_products as vp')
                ->select('vp.product_id')
                ->where('vp.vendor_status', 1)
                ->where('vp.edit_status', 0)
                ->where('vp.approval_status', 1)
                ->groupBy('vp.product_id');

            $divisions = DB::table('products as p')
                ->joinSub($activeVendorProducts, 'active_vendor_products', function ($join) {
                    $join->on('active_vendor_products.product_id', '=', 'p.id');
                })
                ->join('divisions as d', function ($join) {
                    $join->on('p.division_id', '=', 'd.id')
                        ->where('d.status', 1);
                })
                ->join('categories as c', function ($join) {
                    $join->on('p.category_id', '=', 'c.id')
                        ->where('c.status', 1);
                })
                ->where('p.status', 1)
                ->select(
                    'p.division_id',
                    'p.category_id',
                    'd.division_name',
                    'c.category_name'
                )
                ->groupBy(
                    'p.division_id',
                    'p.category_id',
                    'd.division_name',
                    'c.category_name'
                )
                ->orderBy('d.division_name')
                ->orderBy('c.category_name')
                ->get();

            $divisionCategoryData = $this->formatDivisionCategoryData($divisions);

            return view('buyer.layouts.search-by-division', compact('divisionCategoryData'))->render();
        });

        return response()->json([
            // 'status' => true,
            'divisions' => $divisionsHtml,
        ]);
    }

    private function formatDivisionCategoryData(Collection $divisions): array
    {
        return $divisions
            ->groupBy('division_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $categories = $items
                    ->map(function ($item) {
                        return [
                            'category_id' => $item->category_id,
                            'category_name' => $item->category_name,
                        ];
                    })
                    ->unique('category_id')
                    ->sortBy('category_name')
                    ->values()
                    ->all();

                return [
                    'division_id' => $first->division_id,
                    'division_name' => $first->division_name,
                    'categories' => $categories,
                ];
            })
            ->values()
            ->all();
    }
}