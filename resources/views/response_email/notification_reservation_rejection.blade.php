<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status }} Reservasi Kendaraan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: #dc3545;
            color: #ffffff;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            margin: 8px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .info {
            margin-bottom: 15px;
        }
        .info label {
            font-weight: bold;
            color: #dc3545;
        }
        .info p {
            margin: 4px 0;
            font-size: 16px;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background: #007bff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin: 10px 5px 10px 0;
        }
        .btn:hover {
            background: #0056b3;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $status }} Reservasi</h1>
        </div>
        <div class="content">
            <p>Halo {{ $data->user_id != null ? $data->user->name : $data->nama_guest }},</p>
            @if ($status == "Penolakan")
            <p>Sayangnya, kami harus memberitahukan bahwa kendaraan yang Anda reservasi telah dipesan oleh orang lain pada tanggal yang Anda pilih. Kami mohon maaf atas ketidaknyamanan ini.</p>
            @else
            <p>Kami telah menerima permintaan Anda untuk membatalkan reservasi kendaraan dengan nomor transaksi {{ $data->trx_id }}. Berikut adalah detail pembatalan Anda:</p>
            @endif
            
            <div class="info">
                <label>Nomor Transaksi:</label>
                <p>{{ $data->trx_id }}</p>
            </div>
            <div class="info">
                <label>Tanggal Mulai:</label>
                <p>{{ $data->start_date }}</p>
            </div>
            <div class="info">
                <label>Tanggal Selesai:</label>
                <p>{{ $data->end_date }}</p>
            </div>
            <div class="info">
                <label>Waktu Pickup:</label>
                <p>{{ $data->time_pickup }}</p>
            </div>
            <div class="info">
                <label>Nama Kendaraan:</label>
                <p>{{ $data->vehicle->name }}</p>
            </div>
            <div class="info">
                <label>Alasan {{ $status }}:</label>
                <p>{{ $data->reason_canceled }}</p>
            </div>

            @if ($status == "Penolakan")
            <p>Kami menyarankan Anda untuk melakukan reservasi ulang dengan memilih tanggal atau kendaraan lain. Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi kami.</p>
            @else
            <p>Kami telah memproses pembatalan reservasi Anda. Jika Anda memiliki pertanyaan lebih lanjut atau membutuhkan bantuan, silakan hubungi tim dukungan kami.</p>
            @endif

            <a href="{{ route('contact') }}" class="btn">Hubungi Kami</a>
            @if ($status == "Penolakan")
            <a href="{{ route('reservations.create') }}" class="btn">Reservasi Ulang</a>
            @endif
        </div>
        <div class="footer">
            <p>Terima kasih,</p>
            <p>Tim CosmoRent</p>
        </div>
    </div>
</body>
</html>
