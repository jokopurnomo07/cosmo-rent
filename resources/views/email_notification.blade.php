<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
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
            <h1>Reservation Confirmation</h1>
        </div>
        <div class="content">
            <p>Dear {{ $data->user_id != null ? ucwords($data->user->name) : ucwords($data->nama_guest) }},</p>
            <p>We are pleased to confirm your reservation with the following details:</p>
            <ul>
                <li><strong>Reservation ID:</strong> {{ $data->id }}</li>
                <li><strong>Vehicle:</strong> {{ ucwords($data->vehicle->name) }}</li>
                <li><strong>Start Date:</strong> {{ date('d-m-Y', strtotime($data->start_date)) }}</li>
                <li><strong>End Date:</strong> {{ date('d-m-Y', strtotime($data->end_date)) }}</li>
                <li><strong>Total Price:</strong> {{ number_format($data->total_price ?? 0, 0, ',', '.') }}</li>
            </ul>
            <p>To complete your reservation, please proceed with the payment by clicking the button below:</p>
            <a href="{{ $paymentUrl }}" class="button">Pay Now</a>
            <p>If you wish to cancel your reservation, click the button below:</p>
            <a href="{{ route('') }}" class="cancel-button">Cancel Reservation</a>
        </div>
        <div class="footer">
            <p>Thank you for choosing our service!</p>
            <p>If you have any questions, please contact us at <a href="mailto:support@cosmorent.com">support@cosmorent.com</a>.</p>
        </div>
    </div>
</body>
</html>
