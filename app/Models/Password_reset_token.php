<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Password_reset_token extends Model
{
    public $timestamps = false;
    protected $fillable=['email','token','created_at'];
}
