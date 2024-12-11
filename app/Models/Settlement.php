<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'store_id',
        'user_id',
        'start_time',
        'end_time',
        'start_amount',
        'total_amount',
        'expected'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function histoys()
    {
        return $this->hasMany(Histoy::class);
    }
}
