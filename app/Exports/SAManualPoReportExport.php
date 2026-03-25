<?php

namespace App\Exports;

use App\Http\Controllers\Admin\ManualPOReportController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithChunkReading
};
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SAManualPoReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithChunkReading
{
    protected $controller;
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->controller = app()->make(ManualPOReportController::class);
    }

    public function query()
    {
        $request = new Request($this->filters);
        return $this->controller->getFilteredQuery($request);
    }

    public function map($row): array
    {
        $currencySymbol=$this->controller->userCurrency($row->buyer->user_id)['user_currency']['symbol'] ?? '₹';

        $totalAmount = optional($row->products)->sum('product_total_amount') ?? 0;

        return [
            optional($row->buyer)->user->name ?? '',//legal_name korte hbe buyer er jonno
            optional($row->vendor)->legal_name ?? '',
            $this->controller->formatCurrency($totalAmount, $currencySymbol),
            optional($row->created_at)->format('d/m/Y') ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Buyer Name',
            'Vendor Name',
            'PO Value',
            'PO Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths = [];

        foreach ($headings as $index => $heading) {
            $column = chr(65 + $index); // A, B, C, etc.
            $widths[$column] = max(strlen($heading) + 5, 15);
        }

        return $widths;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
