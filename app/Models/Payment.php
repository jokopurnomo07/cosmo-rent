<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, LogsActivity;
    protected static $logAttributes = ['reservation_id', 'amount', 'payment_method', 'status'];
    protected static $logName = 'payment';

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName} model: {$this->name}";
        $activity->user_id = $this->user_id ?? auth()->id() ?? null;
        $activity->user_agent = Request::header('User-Agent');
        $activity->ip_address = Request::ip();
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
