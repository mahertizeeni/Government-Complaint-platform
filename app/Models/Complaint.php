<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;

    // تحديد الجدول إذا كان مختلفًا عن الاسم الافتراضي
    protected $table = 'complaints';

    // تحديد الحقول القابلة للتعبئة
    protected $guarded = ['id'];
     protected $fillable = [
        'title',
        'description',
        'city_id',
        'entity_id',
        'user_id', // إذا كنت تريد ربط الشكوى بالمستخدم
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function city()
    {
    return $this->belongsTo(City::class);
    }

public function entity()
    {
    return $this->belongsTo(Entity::class);
    }
}
