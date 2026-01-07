<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;

class Complaints extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'complaints';
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'booking_id',
        'booking_type'
    ];

    protected $casts = [
         'id' => 'string',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y h:i A');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y h:i A');
    }
}
