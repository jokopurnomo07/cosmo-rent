<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
            <div class="card-body">
                <h4 class="card-title text-center">Verifikasi Email</h4>
                <p class="text-muted text-center">
                    Terima kasih telah mendaftar! Sebelum mulai, silakan verifikasi email Anda dengan mengklik tautan yang telah kami kirimkan.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success text-center" role="alert">
                        Tautan verifikasi baru telah dikirim ke email yang Anda gunakan saat mendaftar.
                    </div>
                @endif

                <div class="d-flex justify-content-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Kirim Ulang Email</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
