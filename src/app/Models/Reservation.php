<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id', 'reservation_slot_id', 'meal', 'snack',
        'status', 'round_type', 'purpose', 'note'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function slot()
    {
        return $this->belongsTo(ReservationSlot::class, 'reservation_slot_id');
    }

    public function attendance()
    {
        return $this->morphOne(Attendance::class, 'reservable');
    }

    public function dateValue()
    {
    // hasOneThroughでreservation_slot経由でdate_valuesを取得
        return $this->hasOneThrough(
            \App\Models\DateValue::class, // 最終的に取得するモデル
            \App\Models\ReservationSlot::class, // 中間モデル
            'id',              // ReservationSlotのPK
            'id',              // DateValueのPK
            'reservation_slot_id', // ReservationのFK
            'date_value_id'        // ReservationSlotのFK
        );
    }
}
