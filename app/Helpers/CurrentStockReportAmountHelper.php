<?php

namespace App\Helpers;
use App\Models\{Grn, Issued, IssuedReturn, ReturnStock};
use Illuminate\Support\Facades\Auth;
class CurrentStockReportAmountHelper
{

    public static function getGrnAmounts(array $inventoryIds, $from = null, $to = null): array
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

        return $query->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($grn) => round($grn->grn_qty * $grn->order_rate,2)))
            ->toArray();
    }
    public static function getIssueAmounts(array $inventoryIds, $from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = Issued::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }
        elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($issue) => round($issue->rate * $issue->qty,2)))
            ->toArray();
    }
    public static function getIssueReturnAmounts(array $inventoryIds, $from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = IssuedReturn::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }
        elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($return) => round($return->rate * $return->qty,2)))
            ->toArray();
    }
    public static function getStockReturnAmounts(array $inventoryIds, $from = null, $to = null): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = ReturnStock::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId);

        if ($from && $to) {
            $query->whereBetween('updated_at', [$from, $to]);
        }
        elseif ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }elseif ($from) {
            $query->whereDate('updated_at', '<', $from);
        }

        return $query->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($return) => round($return->rate * $return->qty,2)))
            ->toArray();
    }
    public static function preloadValueMaps(array $inventoryIds, $from = null, $to = null): array
    {
        return [
            'grn' => self::getGrnAmounts($inventoryIds,$from, $to),
            'issue' => self::getIssueAmounts($inventoryIds,$from, $to),
            'issue_return' => self::getIssueReturnAmounts($inventoryIds,$from, $to),
            'stock_return' => self::getStockReturnAmounts($inventoryIds,$from, $to),
        ];
    }
    public static function calculateAmountValue(
        int $inventoryId,
        float $openingStock,
        float $openingStockPrice,
        array $valueMaps
    ): float|string {
        $initialValue = $openingStock * $openingStockPrice;

        $total = round($initialValue, 2)
        + round(($valueMaps['grn'][$inventoryId] ?? 0), 2)
        - round(($valueMaps['issue'][$inventoryId] ?? 0), 2)
        + round(($valueMaps['issue_return'][$inventoryId] ?? 0), 2)
        - round(($valueMaps['stock_return'][$inventoryId] ?? 0), 2);


        return $total <= 0 ? '0' : round($total, 2);
    }
}
