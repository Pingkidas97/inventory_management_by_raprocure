<?php
namespace App\Exports\Traits;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait ExportStylingTrait
{
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => false,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $headings = $this->headings();
        $finalWidths = [];

        foreach ($headings as $i => $heading) {
            $colLetter = chr(65 + $i);
            $finalWidths[$colLetter] = strlen($heading) + 5;
        }

        return $finalWidths;
    }
    public function chunkSize(): int
    {
        return 1000;
    }
}
