<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\PendingGrnUpdateBYrHelper;
use App\Http\Controllers\Buyer\GrnController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class PendingGrnReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    use ExportStylingTrait;

    protected $currency;
    protected $controller;
    protected $filters;
    protected $updatedByMap;
    protected $collection;
    protected $rowIndex = 0;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(GrnController::class);
    }

    
    public function collection()
    {
        $request = new \Illuminate\Http\Request($this->filters);

        $result = $this->controller->getFilteredPendingGrnData($request);

        if ($result instanceof \Illuminate\Database\Query\Builder) {
            return $result->get();
        }

        return collect($result);
    }
  
    public function map($item): array
    {
        $this->rowIndex++;

        $orderQty = round($item->order_quantity, 2);
        $totalGrnQty = round($item->total_grn_quantity, 2);
        $pendingGrnQty = round($item->pending_quantity, 2);

        return [
            $this->rowIndex,
            $item->branch_name ?? '',
            $item->order_number ?? '',
            $item->order_date ? Carbon::parse($item->order_date)->format('d/m/Y') : '',
            $item->product_name ?? '',
            $item->buyer_product_name ?? '',
            $item->vendor_name ?? '',
            cleanInvisibleCharacters($item->specification ?? ''),
            cleanInvisibleCharacters($item->size ?? ''),
            $item->inventory_grouping ?? '',
            $item->added_by ?? '',
            $item->added_date ? $item->added_date : null,
            $item->uom_name ?? '',
            " " . NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
            " " . NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹'),
            " " . NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
        ];
    }


    public function headings(): array
    {
        return [
            'SN',
            'Branch',
            'Order Number',
            'Order Date',
            'Product Name',
            'Our Product Name',
            'Vendor Name',
            'Specification',
            'Size',
            'Inventory Grouping',
            'Added By',
            'Added Date',
            'UOM',
            'Order Quantity',
            'Total GRN Quantity',
            'Pending GRN Quantity'
        ];
    }


}
