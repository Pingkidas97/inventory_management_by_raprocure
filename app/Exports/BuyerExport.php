<?php

namespace App\Exports;

use App\Models\Buyer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;

class BuyerExport implements FromQuery, WithHeadings, WithChunkReading, ShouldQueue, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Buyer::query()
            ->join('users', 'users.id', '=', 'buyers.user_id');

        if (!empty($this->filters['user'])) {
            $search = $this->filters['user'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('users.status', $this->filters['status']);
        }

        return $query->select(
            'buyers.buyer_code',
            'buyers.legal_name',
            'users.name',
            'users.email',
            'users.mobile',
            'users.is_verified',
            'users.status',
            'users.updated_at'
        );
    }

    public function map($row): array
    {
        return [
            $row->buyer_code,
            $row->legal_name,
            $row->name,
            $row->email,
            $row->mobile,
            $row->is_verified ? 'Yes' : 'No',
            $row->status,
            $row->updated_at ? $row->updated_at->format('d/m/Y H:i') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Buyer Code',
            'Legal Name',
            'User Name',
            'Email',
            'Mobile',
            'Verified',
            'Status',
            'Last Updated',
        ];
    }

    public function chunkSize(): int
    {
        return 100; // Optional: increase chunk size for better performance
    }
}
