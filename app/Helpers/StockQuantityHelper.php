<?php
namespace App\Helpers;

use App\Models\{Grn, Issued, IssuedReturn, ReturnStock};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StockQuantityHelper
{
    public static function getGrnQuantities(array $inventoryIds, $from = null, $to = null): array
    {
        $companyId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = Grn::whereIn('inventory_id', $inventoryIds)
                    ->where('company_id', $companyId);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }
        elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->groupBy('inventory_id')
                    ->selectRaw('inventory_id, SUM(grn_qty) as total')
                    ->pluck('total', 'inventory_id')
                    ->toArray();
    }


    public static function getIssueQuantities(array $inventoryIds,$from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query= Issued::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }

    public static function getIssueReturnQuantities(array $inventoryIds,$from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query= IssuedReturn::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }

    public static function getStockReturnQuantities(array $inventoryIds,$from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query= ReturnStock::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }
    public static function preloadStockQuantityMaps(array $inventoryIds,$from = null, $to = null): array
    {
        try {
            return [
                'grn' => self::getGrnQuantities($inventoryIds,$from, $to),
                'issue' => self::getIssueQuantities($inventoryIds,$from, $to),
                'issue_return' => self::getIssueReturnQuantities($inventoryIds,$from, $to),
                'stock_return' => self::getStockReturnQuantities($inventoryIds,$from, $to),
            ];
        } catch (\Throwable $e) {
            \Log::error('Failed to preload stock quantity maps: ' . $e->getMessage());

            return [
                'grn' => [],
                'issue' => [],
                'issue_return' => [],
                'stock_return' => [],
            ];
        }
    }


    public static function calculateCurrentStockValue(int $inventoryId,float $openingStock,array $quantityMaps): float {
        $total = $openingStock
            + ($quantityMaps['grn'][$inventoryId] ?? 0)
            - ($quantityMaps['issue'][$inventoryId] ?? 0)
            + ($quantityMaps['issue_return'][$inventoryId] ?? 0)
            - ($quantityMaps['stock_return'][$inventoryId] ?? 0);
        return round($total, 3);
    }


    public static function getCurrentStock(
        int $inventoryId,
        float $openingStock,
        int $companyId
    ): float {
        $grn = Grn::where('inventory_id', $inventoryId)
            ->where('company_id', $companyId)
            ->sum('grn_qty');

        $issue = Issued::where('inventory_id', $inventoryId)
            ->where('buyer_id', $companyId)
            ->where('is_deleted', 2)
            ->sum('qty');

        $issueReturn = IssuedReturn::where('inventory_id', $inventoryId)
            ->where('buyer_id', $companyId)
            ->where('is_deleted', 2)
            ->sum('qty');

        $stockReturn = ReturnStock::where('inventory_id', $inventoryId)
            ->where('buyer_id', $companyId)
            ->where('is_deleted', 2)
            ->sum('qty');

        return round(
            $openingStock + $grn - $issue + $issueReturn - $stockReturn,
            2
        );
    }
}
