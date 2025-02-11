<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Exports\RentalsExport;
use Maatwebsite\Excel\Facades\Excel;

class RentalReportController extends Controller
{
    public function index(){
        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('admin.reports', [
            'notifications' => $notifications
        ]);
    }
    public function export(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return Excel::download(new RentalsExport($startDate, $endDate), 'rental_report.xlsx');
    }
}
