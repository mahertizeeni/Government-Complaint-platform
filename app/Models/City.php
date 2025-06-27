<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
}
