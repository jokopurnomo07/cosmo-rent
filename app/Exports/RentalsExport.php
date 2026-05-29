<?php

namespace App\Exports;

use App\Models\Rental;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RentalsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected int $row = 1;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function collection()
    {
        $query = Rental::with([
            'user:id,name,email,phone',
            'vehicle:id,name,type',
            'rental_package:id,name,duration_hours',
        ]);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [
                $this->startDate . ' 00:00:00',
                $this->endDate   . ' 23:59:59',
            ]);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Trx ID',
            'Nama Pemesan',
            'Email',
            'No. Telepon',
            'Kendaraan',
            'Tipe Kendaraan',
            'Paket Sewa',
            'Durasi (Jam)',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Alamat Pickup',
            'Jam Pickup',
            'Total Harga',
            'Status',
        ];
    }

    public function map($rental): array
    {
        return [
            $this->row++,
            $rental->trx_id,
            $rental->user->name               ?? '-',
            $rental->user->email              ?? '-',
            $rental->user->phone              ?? '-',
            $rental->vehicle->name            ?? '-',
            ucfirst($rental->vehicle->type    ?? '-'),
            $rental->rental_package->name     ?? '-',
            $rental->rental_package->duration_hours ?? '-',
            $rental->start_date ? date('d-m-Y H:i', strtotime($rental->start_date)) : '-',
            $rental->end_date   ? date('d-m-Y H:i', strtotime($rental->end_date))   : '-',
            $rental->address_pickup ?? '-',
            $rental->time_pickup    ?? '-',
            $rental->total_price ? 'Rp ' . number_format($rental->total_price, 0, ',', '.') : '-',
            $this->mapStatus($rental->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => '4F81BD'],
                ],
            ],
        ];
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'paid'                  => 'Lunas',
            'ongoing'               => 'Berlangsung',
            'completed'             => 'Selesai',
            'awaiting_confirmation' => 'Menunggu Konfirmasi',
            'payment_failed'        => 'Pembayaran Gagal',
            'returned'              => 'Dikembalikan',
            default                 => ucfirst($status),
        };
    }
}