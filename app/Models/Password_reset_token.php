<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Password_reset_token extends Model
{
    protected $table = 'password_reset_tokens';

    protected $fillable = ['email', 'token', 'created_at'];

    public $timestamps = false;

    public $incrementing = false;
    protected $primaryKey = 'email';
    protected $keyType = 'string';
}
