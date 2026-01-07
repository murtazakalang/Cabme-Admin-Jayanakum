<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RentalPackage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'title',
        'description',
        'image',
        'published',
        'ordering',
        'baseFare',
        'includedHours',
        'includedDistance',
        'extraKmFare',
        'extraMinuteFare',
        'vehicleTypeId',
    ];
    
    protected $casts = [
        'id'=>'string'
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y h:i A');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y h:i A');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicleTypeId');
    }
}
