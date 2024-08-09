<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
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
        $activity->description = "{$eventName} rental for vehicle ID: {$this->vehicle_id} by user ID: {$this->user_id}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
