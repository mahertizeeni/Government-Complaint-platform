<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentEntity extends Model
{
    protected $fillable=['name'];
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
}
