<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservable_id', 'reservable_type', 'actual_start_time', 'actual_end_time',
        'meal_used', 'snack_used',
        'accounted',
    ];

    public function reservable()
    {
        return $this->morphTo();
    }

    public function feeItems()
    {
        return $this->hasMany(AttendanceFeeItem::class);
    }
}
