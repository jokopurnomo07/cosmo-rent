<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory, LogsActivity;
    protected static $logAttributes = ['user_id', 'vehicle_id', 'start_date', 'end_date', 'total_price', 'status'];
    protected static $logName = 'reservation';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'reservation_service');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} reservation for vehicle ID: {$this->vehicle_id} by user ID: {$this->user_id}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
