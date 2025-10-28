<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceFeeItem extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_id', 'fee_item_id', 'amount'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function feeItem()
    {
        return $this->belongsTo(FeeItem::class);
    }
}
