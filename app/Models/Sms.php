<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'phone',
        'message',
        'origin',
        'status',
        'request',
        'response',
        'send_time',
        'request_time',

    ];
    protected $casts = [
        'response' => 'object',
        'request' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }




}
