<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_name',
        'birthday',
        'allergy',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siblings()
    {
        return $this->hasMany(Sibling::class);
    }
}
