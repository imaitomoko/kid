<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sibling extends Model
{
    use HasFactory;

    protected $fillable = [
        'sibling_name',
        'sibling_birthday',
        'sibling_class',
    ];

    public function child()
    {
        return $this->belongsTo(child::class);
    }

    }
