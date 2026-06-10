<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .header h2 { margin: 0; color: #1a73e8; }
        .content { background: #fff; padding: 20px; border: 1px solid #ddd; border-top: none; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; color: #1a73e8; }
        .info-box { background: #f0f4ff; border-left: 4px solid #1a73e8; padding: 15px; margin-bottom: 20px; }
        .button { display: inline-block; background-color: #1a73e8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; text-align: center; }
        .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table td { padding: 10px; border-bottom: 1px solid #eee; }
        table td.label { font-weight: bold; width: 40%; color: #1a73e8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Perpanjangan Rental Disetujui</h2>
        </div>

        <div class="content">
            <p>Halo {{ $user->name }},</p>

            <p>Selamat! Permintaan perpanjangan rental Anda telah <strong>disetujui oleh admin</strong>. Silakan lakukan pembayaran untuk menyelesaikan proses perpanjangan.</p>

            <div class="info-box">
                <strong>⏰ Perpanjangan Hingga:</strong><br>
                {{ $extension->extended_until->format('d M Y, H:i') }}
            </div>

            <div class="section">
                <div class="section-title">Detail Perpanjangan</div>
                <table>
                    <tr>
                        <td class="label">Kendaraan</td>
                        <td>{{ $vehicle->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Durasi Tambahan</td>
                        <td>
                            @php
                                $daysAdded = $extension->extended_until->diffInDays($extension->rental->end_date);
                            @endphp
                            {{ $daysAdded }} hari
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Biaya Perpanjangan</td>
                        <td style="font-weight: bold; font-size: 16px; color: #1a73e8;">
                            Rp {{ number_format($extension->additional_price, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <p style="text-align: center;">
                    <a href="{{ $paymentUrl }}" class="button">Lakukan Pembayaran Sekarang</a>
                </p>
            </div>

            <div class="info-box">
                <strong>📝 Catatan:</strong><br>
                Pembayaran harus diselesaikan dalam waktu 24 jam agar perpanjangan tetap berlaku. Jika tidak, perpanjangan akan otomatis dibatalkan.
            </div>

            <div class="section">
                <p>Jika Anda mengalami kesulitan saat pembayaran, silakan hubungi tim dukungan kami.</p>
                <p>Terima kasih telah menggunakan layanan CosmoRent!</p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} CosmoRent. Semua hak dilindungi.</p>
            <p>Email ini dikirim ke {{ $user->email }}</p>
        </div>
    </div>
</body>
</html>
