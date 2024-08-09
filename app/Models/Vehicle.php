<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
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
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} vehicle: {$this->name} (Model: {$this->model})";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
