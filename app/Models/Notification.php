<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────────────────────────
    // ACCESSORS
    // Prioritas: baca dari kolom langsung dulu, fallback ke data JSON.
    // Ini agar kompatibel dengan dua cara insert (admin manual & event).
    // ─────────────────────────────────────────────────────────────────
    public function getResolvedMessageAttribute(): string
    {
        // Kolom 'message' langsung (dari admin ReservationController)
        if (! empty($this->attributes['message'] ?? null)) {
            return $this->attributes['message'];
        }

        // Fallback: ambil dari data JSON (dari event listener)
        return $this->data['message'] ?? '';
    }

    public function getResolvedTitleAttribute(): string
    {
        if (! empty($this->attributes['title'] ?? null)) {
            return $this->attributes['title'];
        }

        return $this->data['title'] ?? 'Notifikasi';
    }

    public function getReservationIdAttribute(): ?int
    {
        return $this->data['reservation_id'] ?? null;
    }

    public function getTrxIdAttribute(): ?string
    {
        return $this->data['trx_id'] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────────
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}