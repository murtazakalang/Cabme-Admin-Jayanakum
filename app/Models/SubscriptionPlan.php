<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;
    protected $table = 'subscription_plans';
    protected $fillable = [

        'id',
        'bookingLimit',
        'description',
        'expiryDay',
        'image',
        'isEnable',
        'name',
        'place',
        'plan_points',
        'price',
        'type',
        'plan_for',
        'vehicle_limit',
        'driver_limit',
        'dispatcher_access',
    ];
    
    protected $casts = [
        'plan_points' => 'array',
        'id'=>'string'
    ];

    public function subscribers(): HasMany
    {
        return $this->hasMany(Driver::class, 'subscriptionPlanId', 'id');
    }
}
