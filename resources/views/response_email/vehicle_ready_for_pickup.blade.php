<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendaraan Anda Siap Diambil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            padding: 20px;
            background-color: #ffffff;
            margin: 0 auto;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            color: #333333;
        }
        .footer {
            background-color: #f1f1f1;
            padding: 10px;
            text-align: center;
            color: #777777;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .details {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        .details td, .details th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .details th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Kendaraan Anda Siap Diambil</h2>
        </div>
        <div class="content">
            <p>Yth. {{ $rental->user_id != null ? ucwords($rental->user->name) : ucwords($rental->nama_guest) }},</p>

            <p>Kami ingin memberitahukan Anda bahwa kendaraan yang Anda pesan dengan Nomor Transaksi {{ $rental->trx_id }} sudah siap untuk diambil.</p>

            <p>Berikut adalah detail kendaraan:</p>
            <table class="details">
                <tr>
                    <th>Jenis Kendaraan</th>
                    <td>{{ $rental->vehicle->type == "car" ? "Mobil" : "Motor" }}</td>
                </tr>
                <tr>
                    <th>Nama Kendaraan</th>
                    <td>{{ ucwords($rental->vehicle->name) }}</td>
                </tr>
                @if ($rental->services->isNotEmpty())
                    <tr>
                        <th>Layanan</th>
                        <td>
                            @foreach ($rental->services as $service)
                                {{ $service->name }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                @endif
                <tr>
                    <th>Alamat Penjemputan</th>
                    <td>{{ $rental->address_pickup }}</td>
                </tr>
                <tr>
                    <th>Waktu Penjemputan</th>
                    <td>{{ $rental->time_pickup }}</td>
                </tr>
                
            </table>

            <p>Terima kasih telah menggunakan layanan kami.</p>

            <a href="#" class="button">Lihat Detail Pesanan</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Tim Layanan Pelanggan</p>
        </div>
    </div>
</body>
</html>
