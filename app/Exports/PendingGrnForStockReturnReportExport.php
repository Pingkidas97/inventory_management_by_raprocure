<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\PendingGrnStockReturnUpdateBYrHelper;
use App\Http\Controllers\Buyer\GrnController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class PendingGrnForStockReturnReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
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

        // Forcefully convert to Collection
        $result = $this->controller->getFilteredPendingGrnStockReturnData($request);
        $grns = $result instanceof \Illuminate\Database\Eloquent\Builder || $result instanceof \Illuminate\Database\Eloquent\Relations\Relation
            ? $result->get()
            : collect($result);
        // Process and return clean filtered collection
        $this->collection = $grns
            ->unique(fn($item) => $item->inventory_id . '-' . $item->stock_id)            
            ->values();

        return $this->collection;
    }


    public function map($item): array
    {
        $this->rowIndex++;
        $totalGrnQty = round($item->totalGrnQty, 2);
        $orderQty = round($item->stock_qty, 2);
        $pendingGrnQty = round($item->pending_qty, 2);

        $key = $item->inventory_id . '-' . $item->stock_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
        $updatedById = $item->updated_by ?? null;
        $addedByName = $updatedById ? optional(User::find($updatedById))->name : '';

        return [
            " ".$item->grn_no,
            optional($item->inventory->branch)->name ?? '',
            $item->stock_no ?? '',
            optional($item->inventory->product)->product_name ?? '',
            $item->inventory->buyer_product_name ?? '',
            cleanInvisibleCharacters($item->inventory->specification ?? ''),
            cleanInvisibleCharacters($item->inventory->size ?? ''),
            $item->stock_vendor_name ?? '',
            $addedByName,
            Carbon::parse($item->last_updated_at)->format('d/m/Y'),
            optional($item->inventory->uom)->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($orderQty, $this->currency),
            " ".NumberFormatterHelper::formatQty($totalGrnQty, $this->currency),
            " ".NumberFormatterHelper::formatQty($pendingGrnQty, $this->currency),
        ];
    }

    public function headings(): array
    {
        return [
            'GRN Number',
            'Branch',
            'Stock Return No',
            'Product Name',
            'Our Product Name',
            'Specification',
            'Size',
            'Vendor Name',
            'Added By',
            'Added Date',
            'UOM',
            'Stock Return Quantity',
            'Total GRN Quantity',
            'Pending GRN Quantity'
        ];
    }


}
