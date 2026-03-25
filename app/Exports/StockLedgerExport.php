<?php
namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class StockLedgerExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $rowIndex = 0;
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

        $inventoryIds = (clone $originalQuery)->pluck('id')->toArray();
        $this->quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        return $originalQuery ;
    }

    public function map($inv): array
    {
        $this->rowIndex++;
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$this->quantityMaps);
        return [
            $this->rowIndex,
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
        ];
    }


    public function headings(): array
    {
        return [
            'Serial No',
            'Branch Name',
            'Product Name',
            'Category',
            'Our Product Name',
            'Specification',
            'Size',
            'Brand',
            'Inventory Grouping',
            'Current Stock',
            'UOM',
        ];
    }
}
