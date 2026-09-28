<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalStatusLog extends Model
{
    protected $table = 'jurnal_status_log';

    protected $fillable = ['jurnal_id', 'dari_status', 'ke_status', 'user_id', 'waktu'];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}