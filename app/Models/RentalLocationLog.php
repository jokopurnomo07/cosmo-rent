<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentalLocationLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    /**
     * Get latest location log for a rental
     */
    public static function getLatestForRental($rentalId)
    {
        return self::where('rental_id', $rentalId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Get location history for a rental (last 50 points)
     */
    public static function getHistoryForRental($rentalId, $limit = 50)
    {
        return self::where('rental_id', $rentalId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->reverse();
    }
}
