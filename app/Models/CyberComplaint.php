<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CyberComplaint extends Model
{
    protected $fillable=['user_id','type','description','evidence_file','related_link','status'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

