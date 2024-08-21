<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Reservasi Kendaraan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
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
            background: #007bff;
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
        }
        .info {
            margin-bottom: 15px;
        }
        .info label {
            font-weight: bold;
            color: #007bff;
        }
        .info p {
            margin: 4px 0;
            font-size: 16px;
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
            margin-top: 20px;
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
            <h1>Konfirmasi Reservasi</h1>
        </div>
        <div class="content">
            <p>Halo {{ $data->user_id != null ? $data->user->name : $data->nama_guest }},</p>
            <p>Reservasi kendaraan Anda telah dikonfirmasi. Berikut detail reservasi Anda:</p>
            
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
                <label>Total Harga:</label>
                <p>Rp {{ number_format($data->total_price ?? 0, 0, ",", ".") }}</p>
            </div>

            <div class="info">
                <label>Nama Kendaraan:</label>
                <p>{{ $data->vehicle->name }}</p>
            </div>

            <div class="info">
                <label>Kontak:</label>
                <p>Nama: {{ $data->user_id != null ? $data->user->name : $data->nama_guest }}</p>
                <p>Email: {{ $data->user_id != null ? $data->user->email : $data->email_guest }}</p>
                <p>Nomor HP: {{ $data->user_id != null ? $data->user->phone : $data->no_hp_guest }}</p>
                <p>Alamat Pickup: {{ $data->user_id != null ? $data->user->address : $data->address_pickup }}</p>
            </div>

            <p>Jika Anda memiliki pertanyaan atau memerlukan bantuan, silakan hubungi kami.</p>
            
            <a href="{{ route('contact') }}" class="btn">Hubungi Kami</a>
        </div>
        <div class="footer">
            <p>Terima kasih,</p>
            <p>Tim CosmoRent</p>
        </div>
    </div>
</body>
</html>
