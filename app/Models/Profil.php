<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'alamat',
        'jam',
        'no_wa',
        'deskripsi',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
