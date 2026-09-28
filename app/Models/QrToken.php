<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrToken extends Model
{
    protected $table = 'qr_token';

    protected $fillable = ['pembimbing_id', 'token', 'expired_at'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    public function isValid(): bool
    {
        return $this->expired_at->isFuture();
    }
}