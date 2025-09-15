<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_name',
        'relationship',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
