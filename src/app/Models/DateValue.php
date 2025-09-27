<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateValue extends Model
{
    use HasFactory;

    protected $fillable = ['date'];

    public function reservationSlots()
    {
        return $this->hasMany(ReservationSlot::class);
    }
}
