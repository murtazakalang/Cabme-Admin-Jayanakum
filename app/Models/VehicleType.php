<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VehicleType extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $timestamps = false;
    protected $table = 'type_vehicule';
    protected $fillable = [
        'libelle',
        'prix',
        'image',
        'selected_image',
        'creer',
        'modifier'
    ];
    protected $casts = [
        'id' => 'string',
      ];

 
}
