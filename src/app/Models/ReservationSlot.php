<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_value_id',
        'slot_time',
        'capacity',
    ];

    public function dateValue()
    {
        return $this->belongsTo(DateValue::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'reservation_slot_id');
    }
}
