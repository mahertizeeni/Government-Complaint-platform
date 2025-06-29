<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnonymousToken extends Model
{
    protected $fillable = ['token', 'warnings', 'last_submission_at'];

    public function complaints()
    {
        return $this->hasMany(AnonymousComplaint::class);
    }
}
