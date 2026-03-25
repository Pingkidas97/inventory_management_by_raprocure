<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\IndentController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
class IndentReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(IndentController::class);

    }
    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $query = $this->controller->fetchIndentReportDataFilter($request);
        return $query;
    }
    public function map($indent): array
    {
        return [
            $indent->inventory_unique_id,
            optional($indent->inventory->branch)->name ?? '',
            optional($indent->inventory->product)->product_name ?? $indent->inventory->buyer_product_name,
            $indent->inventory->buyer_product_name ?? '',
            cleanInvisibleCharacters($indent->inventory->specification ?? ''),
            cleanInvisibleCharacters($indent->inventory->size ?? ''),
            $indent->inventory->inventory_grouping ?? '',
            optional($indent->updatedBy)->name ?? '',
            " ".NumberFormatterHelper::formatQty($indent->indent_qty, $this->currency) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
            optional($indent->inventory->uom)->uom_name ?? '',
            $indent->remarks ?? '',
            $indent->is_active == '1' ? 'Approved' : 'Unapproved',
            optional($indent->updated_at)->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Indent Number','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','User', 'Indent Quantity','UOM', 'Remarks','Status','Added Date',
        ];
    }
}
