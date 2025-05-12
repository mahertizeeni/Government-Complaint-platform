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
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntity::class);
    }
    public function City()
    {
        return $this->belongsTo(City::class);
    }
}
