<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;

class RentalOrder extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'booking_number',
        'id_user_app',
        'id_conducteur',
        'depart_name',
        'lat_source',
        'lng_source',
        'status',
        'payment_status',
        'id_rental_package',
        'id_vehicle_type',
        'id_payment_method',
        'distance_unit',
        'amount',
        'discount',
        'discount_type',
        'tax',
        'admin_commission',
        'admin_commission_type',
        'rejected_driver_id',
        'transaction_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'otp',
        'current_km',
        'complete_km',
    ];
    
    protected $casts = [
        'id'=>'string'
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

    public function user()
    {
        return $this->belongsTo(UserApp::class, 'id_user_app');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_conducteur');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'id_vehicle_type');
    }

    public function rentalPackage()
    {
        return $this->belongsTo(RentalPackage::class, 'id_rental_package');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

}
