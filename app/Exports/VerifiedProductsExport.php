<?php

namespace App\Exports;

use App\Models\VendorProduct;
use App\Models\ProductAlias;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;

class VerifiedProductsExport implements FromQuery, WithHeadings, WithChunkReading, ShouldQueue, WithMapping
{
    use Exportable;

    protected $filters;
    protected $aliasesByProduct = [];

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = VendorProduct::query()
            ->leftJoin('users', 'users.id', '=', 'vendor_products.vendor_id')
            ->leftJoin('products', 'products.id', '=', 'vendor_products.product_id')
            ->leftJoin('divisions as d', 'd.id', '=', 'products.division_id')
            ->leftJoin('categories as c', 'c.id', '=', 'products.category_id')
            ->leftJoin('users as added_by', 'added_by.id', '=', 'vendor_products.added_by_user_id')
            ->leftJoin('users as verified_by', 'verified_by.id', '=', 'vendor_products.verified_by')
            ->where('vendor_products.edit_status', 0)
            ->where('vendor_products.approval_status', 1)
            ->orderBy('users.name');

        if (!empty($this->filters['product_name'])) {
            $productName = $this->filters['product_name'];
            $query->where('products.product_name', 'like', "%{$productName}%");
        }

        if (!empty($this->filters['vendor_name'])) {
            $vendorName = $this->filters['vendor_name'];
            $query->where('users.name', 'like', "%{$vendorName}%");
        }

        if (!empty($this->filters['status'])) {
            $query->where('users.status', $this->filters['status']);
        }
        // Get product+vendor pairs to preload aliases
        $productVendorPairs = $query->clone()
            ->select('vendor_products.product_id', 'vendor_products.vendor_id')
            ->get();

        $productIds = $productVendorPairs->pluck('product_id')->unique();
        $vendorIds = $productVendorPairs->pluck('vendor_id')->unique();

        // Load aliases in bulk
        $aliases = ProductAlias::whereIn('product_id', $productIds)
            ->whereIn('vendor_id', $vendorIds)
            ->get()
            ->groupBy(function ($alias) {
                return $alias->product_id . '-' . $alias->vendor_id;
            });

        $this->aliasesByProduct = $aliases;

        return $query->select(
            'vendor_products.product_id',
            'vendor_products.vendor_id',
            'users.name as vendor_name',
            'd.division_name as division_name',
            'c.category_name as category_name',
            'products.product_name as product_name',
            'added_by.name as added_by',
            'verified_by.name as verified_by'
        );
    }

    public function map($row): array
    {
        return [
            $row->vendor_name,
            $row->division_name,
            $row->category_name,
            $row->product_name,
            $this->getAliasesByProduct($row->product_id, $row->vendor_id),
            $row->added_by,
            $row->verified_by,
        ];
    }

    public function headings(): array
    {
        return [
            'VENDOR NAME',
            'DIVISION',
            'CATEGORY',
            'PRODUCT NAME',
            'PRODUCT ALIAS',
            'ADDED BY VENDOR',
            'VERIFIED BY',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    protected function getAliasesByProduct($productId, $vendorId = null)
    {
        $key = $productId . '-' . $vendorId;
        if (isset($this->aliasesByProduct[$key]) && $this->aliasesByProduct[$key]->isNotEmpty()) {
            return $this->aliasesByProduct[$key]->pluck('alias')->implode(', ');
        }
        return '';
    }
}
