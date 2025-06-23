<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'government_entity_id',
        'city_id' 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


   public function complaints()
    {
        return $this->hasMany(Complaint::class, 'government_entity_id', 'government_entity_id')
                ->whereColumn('city_id', 'city_id');
    }

    public function suggestion()
{
     return $this->hasMany(Suggestion::class, 'government_entity_id', 'government_entity_id')
                ->whereColumn('city_id', 'city_id');
}


    
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntity::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntity::class);
    }
    public function City()
    {
        return $this->belongsTo(City::class);
    }
}
