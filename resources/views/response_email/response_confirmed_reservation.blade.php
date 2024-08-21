<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Reservasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #0056b3;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .content p {
            margin: 0 0 10px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #218838;
        }
        .cancel-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #dc3545;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Konfirmasi Reservasi</h1>
        </div>
        <div class="content">
            <p>Halo {{ $data->user_id != null ? ucwords($data->user->name) : ucwords($data->nama_guest) }},</p>
            <p>Kami dengan senang hati mengonfirmasi reservasi Anda dengan rincian sebagai berikut:</p>
            <ul>
                <li><strong>ID Reservasi:</strong> {{ $data->trx_id }}</li>
                <li><strong>Kendaraan:</strong> {{ ucwords($data->vehicle->name) }}</li>
                <li><strong>Tanggal Mulai:</strong> {{ date('d-m-Y', strtotime($data->start_date)) }}</li>
                <li><strong>Tanggal Berakhir:</strong> {{ date('d-m-Y', strtotime($data->end_date)) }}</li>
                <li><strong>Total Harga:</strong> Rp{{ number_format($data->total_price ?? 0, 0, ',', '.') }}</li>
            </ul>
            <p>Untuk menyelesaikan reservasi Anda, silakan lanjutkan pembayaran dengan mengklik tombol di bawah ini:</p>
            <a href="{{ $paymentUrl }}" class="button">Bayar Sekarang</a>
            <p>Jika Anda ingin membatalkan reservasi, klik tombol di bawah ini:</p>
            <a href="{{ route('reservations.update-status', ['id' => $data->id, 'status' => 'canceled']) }}" class="cancel-button">Batalkan Reservasi</a>
        </div>
        <div class="footer">
            <p>Terima kasih telah memilih layanan kami!</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi kami di <a href="mailto:support@cosmorent.com">support@cosmorent.com</a>.</p>
        </div>
    </div>
</body>
</html>
