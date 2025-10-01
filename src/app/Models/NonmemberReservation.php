<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonmemberReservation extends Model
{
    use HasFactory;

    protected $table = 'nonmember_reservations';

    protected $fillable = [
        'child_name',
        'is_under_3',
        'date_value_id',
        'start_time',
        'end_time',
        'meal',
        'snack',
        'round_type',
        'purpose',
        'allergy',
        'sibling_class',
        'sibling_name',
        'note',
    ];

    /**
     * 予約スロットとのリレーション
     */
    public function dateValue()
    {
        return $this->belongsTo(DateValue::class, 'date_value_id');
    }

    /**
     * 年齢ラベルを返すアクセサ（便利機能）
     */
    public function getAgeLabelAttribute(): string
    {
        return $this->is_under_3 ? '3歳未満' : '3歳以上';
    }
}
