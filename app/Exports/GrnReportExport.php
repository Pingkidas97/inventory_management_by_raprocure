<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\GrnController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
class GrnReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(GrnController::class);

    }
    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $query = $this->controller->getFilteredQuery($request);
        return $query;
    }
    public function map($grn): array
    {
        
        try {
            $billDate = $grn->bill_date ? \Carbon\Carbon::parse($grn->bill_date)->format('d/m/Y') : '';
        } catch (\Exception $e) {
            $billDate = '';
        }

    
        return [
            $grn->grn_no,
            $grn->po_number . ' ' . (strtotime($grn->created_at) ? \Carbon\Carbon::parse($grn->created_at)->format('d/m/Y') : ''),
            optional($grn->inventory->branch)->name ?? '',
            optional($grn->inventory->product)->product_name ?? '',
            $grn->inventory->buyer_product_name ?? '',
            cleanInvisibleCharacters($grn->inventory->specification ?? ''),
            cleanInvisibleCharacters($grn->inventory->size ?? ''),
            $grn->inventory->inventory_grouping ?? '',
            $grn->vendor_name,
            $grn->vendor_invoice_number,
            $billDate,
            $grn->transporter_name,
            $grn->vehicle_no_lr_no,
            " ".$grn->gross_wt,
            " ".$grn->gst_no,
            " ".$grn->frieght_other_charges,
            $grn->updated_at ? $grn->updated_at->format('d/m/Y') : '',
            optional($grn->updatedBy)->name?? '',
            $grn->updated_at ? $grn->updated_at->format('d/m/Y') : '',
            " ".NumberFormatterHelper::formatQty($grn->grn_qty, $this->currency),
            optional($grn->inventory->uom)->uom_name ?? '',
            " ".NumberFormatterHelper::formatCurrency($grn->order_rate,$this->currency),
            " ".NumberFormatterHelper::formatCurrency($grn->order_rate*$grn->grn_qty,$this->currency),
        ];
    }

    public function headings(): array
    {
        return [
            'Grn Number','Purchase Order','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','Vendor Name', 'Vendor Invoice No','Bill Date','Transporter Name','Vehicle No/ LR No With Date', 'Gross Wt (kgs)','GST ('.$this->currency.')','Frieght / Other Charges ('.$this->currency.')','Gate Entry Number with Date','Added BY','Added Date','GRN Quantity','UOM','Rate('.$this->currency.')','Amounts ('.$this->currency.')'
        ];
    }
}
