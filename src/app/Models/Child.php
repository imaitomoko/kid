<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'child_name',
        'birthday',
        'allergy',
        'gender',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siblings()
    {
        return $this->hasMany(Sibling::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
