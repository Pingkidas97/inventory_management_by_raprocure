<?php

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Str;

if (!function_exists('validate_product_tags')) {
    /**
     * Validate product tags/aliases for uniqueness across the system
     *
     * @param string|null $tags Comma-separated list of tags
     * @param int|null $productId Current product ID (for updates)
     * @param int|null $vendorId Current vendor ID
     * @param bool $isNew Whether this is for a new product
     * @return array Array of error messages
     */
    function validate_product_tags(
        ?string $tags,
        ?int $productId = null,
        ?int $vendorId = null,
        bool $isNew = false
    ): array {
        $errors = [];

        if (empty($tags)) {
            return $errors;
        }

        // Normalize and prepare tags
        $tags = collect(explode(',', $tags))
            ->map(function ($tag) use (&$errors) {
                $normalizedTag = Str::upper(preg_replace('/\s+/', ' ', trim((string) $tag)));

                if ($normalizedTag === '') {
                    return null;
                }

                if (mb_strlen($normalizedTag) > 255) {
                    $errors[] = "<b>{$normalizedTag}</b> exceeds the 255 character limit.";
                    return null;
                }

                return $normalizedTag;
            })
            ->filter()
            ->unique();

        foreach ($tags as $tag) {
            // Check against master products
            $masterProduct = DB::table('products')->where('product_name', $tag)->first();
            if ($masterProduct) {
                $errors[] = "<b>{$tag}</b> is already a Master Product <b>{$masterProduct->product_name}</b>.";
                continue;
            }

            // Check against master product aliases
            $masterAlias = DB::table('product_alias')
                ->where('alias', $tag)
                ->where('alias_of', 1)
                ->first();

            if ($masterAlias) {
                $productName = get_product_name_by_prod_id($masterAlias->product_id);
                $errors[] = "<b>{$tag}</b> already used as alias for Master Product <b>{$productName}</b>.";
                continue;
            }

            // Check against vendor product aliases
            $vendorAliasQuery = DB::table('product_alias')
                ->where('alias', $tag)
                ->where('alias_of', 2);

            if ($isNew) {
                $vendorAliasQuery->where('is_new', true);
            }

            $vendorAliasQuery->where(function ($query) use ($productId, $vendorId) {
                $query->where('product_id', '!=', $productId)
                    ->orWhere('vendor_id', '!=', $vendorId);
            });

            $vendorAlias = $vendorAliasQuery->first();

            if ($vendorAlias && !empty($productId)) {
                $vendorName = get_vendor_name_by_vend_id($vendorAlias->vendor_id);
                $productName = get_product_name_by_prod_id($vendorAlias->product_id);
                $errors[] = "<b>{$tag}</b> already used by Vendor <b>{$vendorName}</b> as an alias for Product <b>{$productName}</b>.";
                continue;
            }
        }

        return $errors;
    }
}

if (!function_exists('get_product_name_by_prod_id')) {
    function get_product_name_by_prod_id(int $productId): ?string
    {
        return Product::where('id', $productId)->value('product_name');
    }
}

if (!function_exists('get_vendor_name_by_vend_id')) {
    function get_vendor_name_by_vend_id(int $vendorId): ?string
    {
        return DB::table('users')->where('id', $vendorId)->value('name');
    }
}

if (!function_exists('get_alias_master_by_prod_id')) {
    function get_alias_master_by_prod_id($prod_id) {
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 1)
            ->where('is_new', 1)
            ->where('product_id', $prod_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        return implode(', ', $aliases);
    }
}

if (!function_exists('get_alias_vendor_by_prod_id')) {
    function get_alias_vendor_by_prod_id($prod_id, $vend_id) {
        // Fetch aliases from the database
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 2)
            ->where('is_new', 1)
            ->where('product_id', $prod_id)
            ->where('vendor_id', $vend_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        // Concatenate aliases into a single string
        return implode(', ', $aliases);
    }
}

if (!function_exists('get_new_alias_vendor_by_prod_id')) {
    function get_new_alias_vendor_by_prod_id($prod_id, $vend_id) {
        // Fetch aliases from the database
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 2)
            ->where('is_new',null)
            ->where('product_id', $prod_id)
            ->where('vendor_id', $vend_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        // Concatenate aliases into a single string
        return implode(', ', $aliases);
    }
}




if (!function_exists('get_active_dealer_types')) {
    function get_active_dealer_types()
    {
        return DB::table('dealer_types')
            ->where('status', '1')
            ->orderBy('dealer_type')
            ->get();
    }
}

if (!function_exists('get_active_uoms')) {
    function get_active_uoms()
    {
        return DB::table('uoms')
            ->where('status', '1')
            ->orderBy('uom_name')
            ->get();
    }
}


if (!function_exists('get_active_tax')) {

    function get_active_tax()
    {
        return DB::table('taxes')
            ->where('status', '1')
            ->orderBy('id')
            ->get();
    }
}


if (!function_exists('createSlug')) {

    function createSlug($string)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}

if(!function_exists('normalizeSingularPlural')) {
    // Helper function to normalize singular and plural forms
    function normalizeSingularPlural($word) {
        // Simple normalization for plural/singular forms (basic heuristic)
        if (substr($word, -1) === 's') {
            return rtrim($word, 's'); // Remove trailing 's'
        }
        return $word;
    }
}

if (!function_exists('mng_best_match_for_multiple')) {
    function mng_best_match_for_multiple($inputData, $storedDataArray)
    {
        $inputData = mng_normalize_string(sanitizePNameForBulkProduct($inputData));

        $bestMatch = null;
        $bestMatchScore = 0;
        $bestMatchDetails = "";

        foreach ($storedDataArray as $storedData) {
            $isPrefixMatch = (stripos($inputData, $storedData) === 0);
            $levenshteinDistance = levenshtein(strtoupper($storedData), strtoupper($inputData));
            similar_text(strtoupper($storedData), strtoupper($inputData), $similarityPercent);

            $matchScore = 0;

            if ($isPrefixMatch) {
                $matchScore += 10;
            }

            if ($levenshteinDistance < 10) {
                $matchScore += 5;
            }

            if ($similarityPercent > 70) {
                $matchScore += 5;
            }

            $normalizedInput = mng_normalize_string($inputData);
            $normalizedStoredData = mng_normalize_string($storedData);
            if ($normalizedInput === $normalizedStoredData) {
                $matchScore += 15;
            }

            if ($matchScore > $bestMatchScore) {
                $bestMatchScore = $matchScore;
                $bestMatch = $storedData;
                $bestMatchDetails = "Levenshtein distance: $levenshteinDistance, Similarity: $similarityPercent%";
            }
        }

        return $bestMatch ?: '';
    }
}
function mng_normalize_string($str) {
    // Split the string into words, sort them, and return the joined string
    $words = explode(' ', strtoupper($str));  // Convert to uppercase for case-insensitive comparison
    sort($words);
    return implode(' ', $words);
}

if (!function_exists('sanitizePNameForBulkProduct')) {
    function sanitizePNameForBulkProduct($p_name) {
        $p_name = strtolower($p_name);

        // Use a regular expression to match words with numbers
        // $p_name = preg_replace('/\b\w*\d\w*\b/', '', $p_name);
        // Remove extra spaces (including multiple spaces)
        // $p_name = preg_replace('/\s+/', ' ', $p_name);

        $p_name = str_replace(array('~', '`', '!', "@", '#', '$', '%', '^', '&', '*', '(', ')', '_', '-', '+', '=', '|', '\/', '{', '[', ']', '}', ':', ';', '"', "'", ',', '<', '>', '.', '?', '/'), '', $p_name);

        // Use a regular expression to match words with numbers
        $p_name = preg_replace('/\b\w*\d\w*\b/', '', $p_name);

        $p_name = str_replace(array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'), '', $p_name);

        // Remove extra spaces (including multiple spaces)
        $p_name = preg_replace('/\s+/', ' ', $p_name);
        return trim($p_name);
    }
}

if(!function_exists('findUomByUomAlias'))
{
    function findUomByUomAlias($uom)
    {
        $uom = strtolower(trim($uom));
        $uom_alias = array(
            '1'=>array('nos', 'number', 'pcs', 'pices', 'no', 'coi', 'coil', 'eac', 'each', 'unit'),//Pieces
            '2'=>array('set', 'pr', 'pairs', 'pair'),//Sets
            '3'=>array('metres', 'mtr', 'mtrs', 'metre', 'meter', 'rmt', 'runing meter'),//Metre
            '4'=>array('mt', 'mattric ton', 'm.t', 'ton', 'metric ton', 'm.t.'),//MT
            '5'=>array('kg', 'kilogram','kilo','kilo grams','kilograms','gram','grams','kgs'),//Kgs
            '6'=>array('liter', 'litres', 'ltr'),//Litre
            '7'=>array('pkg', 'pckg', 'packin', 'package', 'packages', 'box', 'pkt', 'bag', 'bnd', 'bundle', 'brl', 'barrel', 'btl', 'bottle', 'can', 'cans', 'cas', 'case', 'con', 'container', 'crt', 'crate', 'drm', 'drum', 'packet', 'rol', 'roll'),//Packages
        );
        $uom_id = 0;
        foreach ($uom_alias as $key => $value) {
            if(in_array($uom, $value)){
                $uom_id = $key;
                break;
            }
        }
        return $uom_id;
    }
}