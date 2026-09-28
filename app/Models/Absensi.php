<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'siswa_id', 'penempatan_id', 'qr_token_id', 'tanggal',
        'jam_masuk', 'jam_pulang', 'status', 'keterangan', 'lampiran',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function penempatan()
    {
        return $this->belongsTo(PenempatanPkl::class);
    }

    public function qrToken()
    {
        return $this->belongsTo(QrToken::class);
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }
}