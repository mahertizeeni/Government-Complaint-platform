<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = [
    'user_id',
    'government_entity_id',
    'city_id',
    'title',
    'description',
];
public function user()
{
    return $this->belongsTo(User::class);
}

public function governmentEntity()
{
    return $this->belongsTo(GovernmentEntity::class);
}

public function city()
{
    return $this->belongsTo(City::class);
}

}
