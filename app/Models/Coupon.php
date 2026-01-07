<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    public $timestamps = false;
    
    protected $casts = [
        'id'=>'string',
    ];
    
    protected $fillable = [
        'code',
        'discount',
        'type',
        'coupon_type',
    ];
}
