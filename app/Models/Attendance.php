<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','type','tanggal','waktu','photo','lat','lng','note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
