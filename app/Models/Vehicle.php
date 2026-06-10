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

    protected $casts = [
        'price_4_hours' => 'float',
        'price_12_hours' => 'float',
        'price_24_hours' => 'float',
    ];    

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'vehicle_features');
    }

    public function prices()
    {
        return $this->hasOne(VehiclePrice::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'vehicle_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'vehicle_id');
    }

    /**
     * Calculate current available units
     * Available = stock_quantity - active_rentals - confirmed_reservations
     */
    public function getAvailableCount()
    {
        $activeRentals = $this->rentals()
            ->whereIn('status', ['paid', 'ongoing'])
            ->count();

        $confirmedReservations = $this->reservations()
            ->whereIn('status', ['confirmed', 'paid'])
            ->count();

        $available = max(0, $this->stock_quantity - $activeRentals - $confirmedReservations);
        
        return $available;
    }

    /**
     * Update current available count in database
     */
    public function updateAvailableCount()
    {
        $this->current_available_count = $this->getAvailableCount();
        $this->save();
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
