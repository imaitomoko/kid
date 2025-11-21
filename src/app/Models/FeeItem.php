<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'unit',
        'amount',
        'start_date',
        'end_date',
    ];

    public function scopeActiveOn($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            });
    }

    public static function getValidFee($category, $date)
    {
        return self::where('category', $category)
            ->activeOn($date)
            ->first();
    }
}
