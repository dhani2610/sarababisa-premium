<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'users_id',
        'workers_id',
        'bonus',
        'created_at',
        'cabang_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workers_id', 'id');
    }
}
