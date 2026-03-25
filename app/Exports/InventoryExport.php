<?php
namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class InventoryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $quantityMaps;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(InventoryController::class);
    }

    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $originalQuery = $this->controller->applyFilters($request);

        $inventoryIds =  (clone $originalQuery)->pluck('id')->toArray();
        $this->quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $this->controller->preloadGrnData($inventoryIds);
        $this->controller->preloadRfqData($inventoryIds);
        $this->controller->preloadOrderData($inventoryIds);
        return $originalQuery ;
    }

    public function map($inv): array
    {
        $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->sum('indent_qty');
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$this->quantityMaps);
        $rfq_qty = $this->controller->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0;
        $order_qty = $this->controller->getOrderData($inv->id)['order_qty'][$inv->id] ?? 0;
        $grn_qty = $this->controller->getGrnData($inv->id)['grn_qty'][$inv->id] ?? 0;

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
            $inv->branch->name ?? '',
            $inv->product->product_name ?? $inv->buyer_product_name,
            $inv->product->category->category_name ?? '',
            $inv->buyer_product_name,
            cleanInvisibleCharacters($inv->specification),
            cleanInvisibleCharacters($inv->size),
            $inv->product_brand,
            $inv->inventory_grouping,
            " ".NumberFormatterHelper::formatQty($currentStockValue, $this->currency),
            $inv->uom->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($indentQty, $this->currency),
            " ".$formattedRFQQty,
            " ".$formattedOrderQty,
            " ".$formattedGrnQty,
        ];
    }

    public function headings(): array
    {
        return [
            'Branch', 'Product', 'Category', 'Our Product Name', 'Specification',
            'Size', 'Brand', 'Inventory Grouping', 'Current Stock', 'UOM',
            'Indent Qty', 'RFQ Qty', 'Order Qty', 'GRN Qty',
        ];
    }


}
