<?php
namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class MinQtyExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
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
        $originalQuery = $this->controller->applyFiltersMinQty($request);

        $inventoryIds =  (clone $originalQuery)->pluck('id')->toArray();
        $this->quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        return $originalQuery ;
    }

    public function map($inv): array
    {
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$this->quantityMaps);
        
        return [
            $inv->branch->name ?? '',
            $inv->product->product_name ?? '',
            $inv->product->category->category_name ?? '',
            $inv->buyer_product_name,
            cleanInvisibleCharacters($inv->specification),
            cleanInvisibleCharacters($inv->size),
            " ".NumberFormatterHelper::formatQty($currentStockValue, $this->currency),
            " ".NumberFormatterHelper::formatQty($inv->indent_min_qty, $this->currency),
            optional($inv->updatedBy)->name ?? optional($inv->createdBy)->name ?? '',
            $inv->updated_at?->format('d/m/Y') ?? $inv->created_at?->format('d/m/Y') ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Branch', 'Product', 'Category', 'Our Product Name', 'Specification',
            'Size', 'Current Stock', 'Min Qty',
            'Added / Updated By', 'Added / Updated Date',
        ];
    }


}
