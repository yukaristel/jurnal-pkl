<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catatan extends Model
{
    protected $table = 'catatan';

    protected $fillable = [
        'jurnal_id', 'siswa_id', 'pengirim_id', 'tipe', 'isi', 'dibaca_at',
    ];

    protected $casts = [
        'dibaca_at' => 'datetime',
    ];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}