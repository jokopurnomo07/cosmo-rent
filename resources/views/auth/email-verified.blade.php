<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - Cosmo Rent</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f8f8f8;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 400px;
            width: 90%;
        }
        .icon {
            font-size: 52px;
            margin-bottom: 16px;
        }
        h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 10px;
        }
        p {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .countdown {
            font-size: 13px;
            color: #aaa;
        }
        .countdown span {
            font-weight: 600;
            color: #7367f0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h2>Email Berhasil Diverifikasi!</h2>
        <p>Akun Anda telah aktif. Tab ini akan tertutup otomatis.</p>
        <p class="countdown">Menutup dalam <span id="timer">3</span> detik...</p>
    </div>

    <script>
        let seconds = 3;
        const timerEl = document.getElementById('timer');

        const countdown = setInterval(() => {
            seconds--;
            timerEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdown);
                window.close();

                // Fallback: if window.close() is blocked by browser
                // (some browsers block closing tabs not opened by script)
                setTimeout(() => {
                    document.querySelector('p').textContent =
                        'Silakan tutup tab ini secara manual dan kembali ke halaman sebelumnya.';
                    document.querySelector('.countdown').style.display = 'none';
                }, 500);
            }
        }, 1000);
    </script>
</body>
</html>