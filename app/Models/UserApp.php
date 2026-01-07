<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserApp extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;
    
    protected $table = 'user_app';
    
    protected $fillable = [
        'nom',
        'email',
        'prenom',
        'email',
        'phone',
        'country_code',
        'mdp',
        'login_type',
        'photo',
        'photo_path',
        'photo_nic',
        'photo_nic_path',
        'statut',
        'statut_nic',
        'tonotify',
        'device_id',
        'fcm_id',
        'creer',
        'modifier',
        'amount',
        'reset_password_otp',
        'reset_password_otp_modifier',
        'age',
        'gender',
        'review_sum',
        'review_count',
        'average_rating',
    ];

    protected $casts = [
         'id' => 'string',
    ];
}

