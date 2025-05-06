<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // لو جدولك اسمه employees فما في داعي لتعريف $table
    // protected $table = 'employees';

    protected $fillable = [
        'name',
        'email',
        'password',
        'intity', // المؤسسة التابع لها الموظف
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // علاقة الموظف مع الشكاوى في مؤسسته
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'intity', 'intity');
    }
}
