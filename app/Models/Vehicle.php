<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    protected static $logAttributes = ['name', 'brand', 'model', 'year', 'status'];
    protected static $logName = 'vehicle';

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'vehicle_features');
    }

    public function prices()
    {
        return $this->hasOne(VehiclePrice::class);
    }


    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} vehicle: {$this->name} (Model: {$this->model})";
        $activity->user_id = $this->user_id ?? auth()->id() ?? null;
        $activity->user_agent = Request::header('User-Agent');
        $activity->ip_address = Request::ip();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
