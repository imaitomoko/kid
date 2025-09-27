<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'actual_start_time', 'actual_end_time',
        'meal_used', 'snack_used'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function feeItems()
    {
        return $this->hasMany(AttendanceFeeItem::class);
    }
}
