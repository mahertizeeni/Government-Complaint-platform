<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable =[
        'id',
        'name',
        'status',
    ];
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
}
