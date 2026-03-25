<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\ManualPOController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
class ManualPoReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $rowIndex = 0;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(ManualPOController::class);

    }
    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $query = $this->controller->getFilteredQuery($request);
        return $query;
    }
    public function map($row): array
    {
        $this->rowIndex++;
        $product = $row->products->first();
        $currency_symbol = $row->currencyDetails?->currency_symbol ?? (session('user_currency')['symbol'] ?? '₹');
            
        return [
            $this->rowIndex,
            optional($product->inventory->branch)->name ?? '',
            $row->manual_po_number,
            optional($row->created_at)->format('d/m/Y'),
            $this->controller->formatProductName($row, $this->filters['search_product_name'] ?? '', $this->filters['search_category_id'] ?? ''),
            optional($row->vendor)->legal_name ?? '',
            optional($row->preparedBy)->name ?? '',
            NumberFormatterHelper::formatCurrency($row->products->sum('product_total_amount'), $currency_symbol),
            $row->order_status == 1 ? 'Confirmed' : 'Cancelled',
        ];
    }

    public function headings(): array
    {
        return [
           'Serial Number','Branch','Order Number','Order Date','Product Name','Vendor Name','Added BY','Order Value','Status'
        ];
    }
}
