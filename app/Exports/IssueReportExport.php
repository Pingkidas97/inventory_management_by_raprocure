<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\IssuedController;
use Carbon\Carbon;
use App\Models\Issued;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class IssueReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(IssuedController::class);
    }

    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        if($request->filled('search_consume')){
            $query= $this->controller->applyFiltersForConsume($request);            

            $inventoryIds = (clone $query)->pluck('inventory_id')->toArray();
            $this->consumeAmountTotals = Issued::with([
                    'inventory',
                    'grn'
                ])
                ->whereIn('inventory_id', $inventoryIds)
                ->where('consume_qty', '>', 0)
                ->get()
                ->groupBy('inventory_id')
                ->map(function ($group) {
                    return $group->sum(function ($item) {
                        return $item->consume_amount;   // accessor usage
                    });
                })
                ->toArray();
        
            return $query;
        }else{
            return $this->controller->applyFilters($request);
        }
        
    }

    public function map($row): array
    {
        $request = new \Illuminate\Http\Request($this->filters);
        if($request->filled('search_consume')){
            $inventoryId = $row->inventory_id;

            $totalConsumeAmount = $this->consumeAmountTotals[$inventoryId] ?? 0;
            return [
                optional($row->inventory->branch)->name ?? '',
                $row->inventory->product->product_name ?? '',
                $row->inventory->buyer_product_name ?? '',
                $row->inventory->product->division->division_name ?? '',
                $row->inventory->product->category->category_name ?? '',
                $row->inventory->specification ?? '',
                $row->inventory->size ?? '',
                $row->inventory->inventory_grouping ?? '',
                " " . NumberFormatterHelper::formatQty($row->total_consume_qty, $this->currency),
                $row->inventory->uom->uom_name ?? '',
                NumberFormatterHelper::formatCurrency($totalConsumeAmount, $this->currency),
            ];
        }else{
            return [
                $row->issued_no,
                optional($row->inventory->branch)->name ?? '',
                $row->inventory->product->product_name ?? '',
                $row->inventory->buyer_product_name ?? '',
                $row->inventory->product->division->division_name ?? '',
                $row->inventory->product->category->category_name ?? '',
                cleanInvisibleCharacters($row->inventory->specification ?? ''),
                cleanInvisibleCharacters($row->inventory->size ?? ''),
                $row->inventory->inventory_grouping ?? '',
                " " . NumberFormatterHelper::formatQty($row->qty, $this->currency),
                $row->inventory->uom->uom_name ?? '',
                NumberFormatterHelper::formatCurrency($row->amount, $this->currency),
                $row->updater->name ?? '',
                $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
                cleanInvisibleCharacters($row->remarks ?? ''),
                $row->issuedTo->name ?? '',
            ];
        }

    }

    public function headings(): array
    {
        $request = new \Illuminate\Http\Request($this->filters);
        if($request->filled('search_consume')){
            return [
                'Branch',
                'Product Name',
                'Our Product Name',
                'Division',
                'Category',
                'Specification',
                'Size',
                'Inventory Grouping',
                'Consume Quantity',
                'UOM',
                'Consume Amount (' . $this->currency . ')',                
            ];
        }else{
            return [
                'Issue Number',
                'Branch',
                'Product Name',
                'Our Product Name',
                'Division',
                'Category',
                'Specification',
                'Size',
                'Inventory Grouping',
                'Issued Quantity',
                'UOM',
                'Amount (' . $this->currency . ')',
                'Added BY',
                'Added Date',
                'Remarks',
                'Issued To'
            ];
        }
        
    }
}
