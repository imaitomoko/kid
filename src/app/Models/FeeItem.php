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
}
