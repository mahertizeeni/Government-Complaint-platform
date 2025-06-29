<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnonymousComplaint extends Model
{
    protected $fillable = ['anonymous_token_id', 'message', 'submitted_at'];

    public function token()
    {
        return $this->belongsTo(AnonymousToken::class, 'anonymous_token_id');
    }
}
