<?php
namespace App\Exports;

use App\Models\Rental;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RentalsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Rental::with('user');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['No', 'Trx ID', 'Nama Pemesan', 'Tanggal Sewa', 'Tanggal Selesai', 'Status', 'Pembayaran'];
    }

    public function map($rental): array
    {
        return [
            $rental->id,
            $rental->trx_id,
            $rental->user->name ?? 'Guest',
            date('d-m-Y', strtotime($rental->start_date)),
            date('d-m-Y', strtotime($rental->end_date)),
            ucfirst($rental->status),
            ucfirst($rental->payment_status),
        ];
    }
}
