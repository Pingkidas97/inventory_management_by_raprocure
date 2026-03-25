<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
class DeadStockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $quantityMaps;
    protected $rowIndex = 0;
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
        $originalQuery = $this->controller->deadStockApplyFilters($request);
        return $originalQuery ;
    }
    public function map($inv): array
    {
        $this->rowIndex++;
        return [
            $this->rowIndex,
            $inv->branch->name ?? '',
            $inv->product->product_name ?? '',
            $inv->buyer_product_name,
            cleanInvisibleCharacters($inv->specification),
            cleanInvisibleCharacters($inv->size),
            $inv->inventory_grouping,
            $inv->uom->uom_name ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Serial No','Branch', 'Product Name', 'Our Product Name', 'Specification',
            'Size','Inventory Grouping','UOM',
        ];
    }
}

