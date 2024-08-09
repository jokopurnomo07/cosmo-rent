<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory, LogsActivity;
    protected static $logAttributes = ['user_id', 'vehicle_id', 'package_id', 'start_date', 'end_date', 'total_price', 'down_payment_amount', 'status'];
    protected static $logName = 'rental';

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} rental for vehicle ID: {$this->vehicle_id}";
        $activity->user_id = $this->user_id ?? auth()->id() ?? null;
        $activity->user_agent = Request::header('User-Agent');
        $activity->ip_address = Request::ip();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
