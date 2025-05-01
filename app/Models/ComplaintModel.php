<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplaintModel extends Model
{
    use HasFactory;

    // تحديد الجدول إذا كان مختلفًا عن الاسم الافتراضي
    protected $table = 'complaints';

    // تحديد الحقول القابلة للتعبئة
    protected $fillable = [
        'title',
        'description',
        'user_id', // إذا كنت تريد ربط الشكوى بالمستخدم
    ];

    // إذا كنت تريد تحديد العلاقة مع نموذج المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
