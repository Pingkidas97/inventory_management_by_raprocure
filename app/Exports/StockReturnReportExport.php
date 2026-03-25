<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\StockReturnController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class StockReturnReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(StockReturnController::class);
    }

    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        return $this->controller->applyFilters($request);
    }

    public function map($row): array
    {
        return [
            $row->stock_no,
            optional($row->inventory->branch)->name ?? '',
            $row->inventory->product->product_name ?? '',
            $row->inventory->buyer_product_name ?? '',
            cleanInvisibleCharacters($row->inventory->specification ?? ''),
            cleanInvisibleCharacters($row->inventory->size ?? ''),
            $row->inventory->inventory_grouping ?? '',
            $row->returnType->name ?? '',
            $row->updater->name ?? '',
            $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
            " " . NumberFormatterHelper::formatQty($row->qty, $this->currency),
            $row->inventory->uom->uom_name ?? '',
            cleanInvisibleCharacters($row->remarks ?? ''),
        ];
    }

    public function headings(): array
    {
        return ['Stock Number','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','Return Type','Added BY','Added Date','Quantity','UOM','Remarks'
        ];
    }
}
