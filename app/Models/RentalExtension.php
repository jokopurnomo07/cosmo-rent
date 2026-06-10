<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentalExtension extends Model
{
    use HasFactory, LogsActivity;
    
    protected static $logAttributes = ['rental_id', 'extended_until', 'additional_price', 'status'];
    protected static $logName = 'rental_extension';
    protected $guarded = [];
    protected $casts = [
        'extended_until' => 'datetime',
        'payment_due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $rental = $this->rental;
        $activity->description = "{$eventName} extension for rental ID: {$this->rental_id}";
        $activity->user_id = $rental?->user_id ?? auth()->id() ?? null;
        $activity->user_agent = Request::header('User-Agent');
        $activity->ip_address = Request::ip();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    /**
     * Check if extension can be created for this rental
     * Rules: Only 1 extension per rental, max 7 days, rental must be ongoing
     */
    public static function canExtend($rental)
    {
        if ($rental->status !== 'ongoing' && $rental->status !== 'paid') {
            return false;
        }

        // Check if extension already exists
        $existingExtension = self::where('rental_id', $rental->id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->exists();

        return !$existingExtension;
    }
}
