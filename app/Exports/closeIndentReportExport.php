<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\IndentController;
use Maatwebsite\Excel\Concerns\{
    WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading,FromCollection
};
class closeIndentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
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
        $this->controller = app()->make(IndentController::class);

    }
    public function collection()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        return $this->controller->closeIndentReportDataFilter($request);
    }
    public function map($indent): array
    {
        $this->rowIndex++;
        $rfq_qty = $this->controller->getRfqData($indent->inventory->id)['rfq_qty'][$indent->inventory->id] ?? 0;
        $order_qty = $this->controller->getOrderData($indent->inventory->id)['order_qty'][$indent->inventory->id] ?? 0;
        $grn_qty = $this->controller->getGrnData($indent->inventory->id)['grn_qty'][$indent->inventory->id] ?? 0;
        $formattedRFQQty = $rfq_qty > 0
            ? NumberFormatterHelper::formatQty($rfq_qty, $this->currency)
            : 0;
        $formattedOrderQty = $order_qty > 0
            ? NumberFormatterHelper::formatQty($order_qty, $this->currency)
            : 0;
        $formattedGrnQty = $grn_qty > 0
            ? NumberFormatterHelper::formatQty($grn_qty, $this->currency)
            : 0;
        return [
            $this->rowIndex,
            optional($indent->inventory->branch)->name ?? '',
            optional($indent->inventory->product)->product_name ?? '',
            $indent->inventory->buyer_product_name ?? '',
            cleanInvisibleCharacters($indent->inventory->specification ?? ''),
            cleanInvisibleCharacters($indent->inventory->size ?? ''),
            $indent->inventory->inventory_grouping ?? '',
            optional($indent->updatedBy)->name ?? '',
            optional($indent->inventory->uom)->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($indent->indent_qty, $this->currency) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
            " ".$formattedRFQQty,
            " ".$formattedOrderQty,
            " ".$formattedGrnQty,
        ];
    }

    public function headings(): array
    {
        return [
            'Serial Number','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','User','UOM','Indent Qty','RFQ Qty','Order Qty','GRN Qty',
        ];
    }
}
