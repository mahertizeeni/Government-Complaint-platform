<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $guarded = ['id'];
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntity::class);
    }
    public function City()
    {
        return $this->belongsTo(City::class);
    }
}
