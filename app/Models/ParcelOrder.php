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

class ParcelOrder extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'parcel_orders';

    protected $fillable = [

        'id_user_app',
        'id_conducteur',
        'source',
        'destination',
        'lat_source',
        'lng_source',
        'lat_destination',
        'lng_destination',
        'sender_name',
        'sender_phone',
        'receiver_name',
        'receiver_phone',
        'parcel_weight',
        'parcel_dimension',
        'parcel_image',
        'parcel_type',
        'parcel_date',
        'parcel_time',
        'receive_date',
        'receive_time',
        'status',
        'reason',
        'note',
        'payment_status',
        'id_payment_method',
        'distance',
        'distance_unit',
        'amount', 
        'discount',
        'discount_type',
        'tax',
        'tip',
        'admin_commission',
        'admin_commission_type',
        'otp',
        'assigned_driver_id',
        'rejected_driver_id',
        'ownerId',
        'transaction_id',
        'booking_number',
    ];

    protected $casts = [
        'id'=>'string',
    ];
    
    public function user()
    {
        return $this->belongsTo(UserApp::class, 'id_user_app');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_conducteur');
    }

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

