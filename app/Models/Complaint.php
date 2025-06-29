<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;

    protected $table = 'complaints';

    protected $guarded = ['id'];
     protected $fillable = [
        'title',
        'description',
        'city_id',
        'government_entity_id',
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

public function governmentEntity()
    {
    return $this->belongsTo(GovernmentEntity::class);
    }
    public function handled_by()
{
    return $this->belongsTo(Employee::class, 'handled_by_id'); // أو Admin::class
}

}
