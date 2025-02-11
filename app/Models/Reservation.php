<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $guarded = [];
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

    public function rental_package()
    {
        return $this->belongsTo(RentalPackage::class, 'rental_package_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'reservation_services');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} reservation for vehicle ID: {$this->vehicle_id}";
        $activity->user_id = $this->user_id ?? auth()->id() ?? null;
        $activity->user_agent = Request::header('User-Agent');
        $activity->ip_address = Request::ip();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
