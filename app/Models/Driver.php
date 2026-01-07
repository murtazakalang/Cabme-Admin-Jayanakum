<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Driver extends Authenticatable
{

  use HasApiTokens, HasFactory, Notifiable;

  public $timestamps = false;
  
  protected $table = 'conducteur';

  protected $fillable = [

    'nom',
    'email',
    'prenom',
    'phone',
    'country_code',
    'mdp',
    'latitude',
    'longitude',
    'statut_vehicule',
    'status_car_image',
    'online',
    'login_type',
    'creer',
    'modifier',
    'subscriptionPlanId',
    'subscriptionExpiryDate',
    'subscriptionTotalOrders',
    'subscriptionTotalVehicle',
    'subscriptionTotalDriver',
    'subscription_plan',
    'statut',
    'driver_on_ride',
    'role',
    'companyName',
    'isOwner',
    'ownerId',
    'review_sum',
    'review_count',
    'average_rating',
    'zone_id',
    'service_type',
    'adminCommission',
    'is_verified',
    'tonotify',
    'address',
    'amount',
  ];

  protected $casts = [
    'id' => 'string',
    'subscription_plan' => 'array',
    'adminCommission' => 'array'
  ];

  public function subscriptionPlan(): BelongsTo
  {
    return $this->belongsTo(SubscriptionPlan::class, 'subscriptionPlanId', 'id');
  }
}
