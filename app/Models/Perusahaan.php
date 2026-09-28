<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $table = 'perusahaan';

    protected $fillable = [
        'nama', 'alamat', 'bidang_usaha', 'telepon',
        'latitude', 'longitude', 'radius_meter',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_meter' => 'integer',
    ];

    public function pembimbing()
    {
        return $this->hasMany(Pembimbing::class);
    }

    public function pengaturanAbsensi()
    {
        return $this->hasMany(PengaturanAbsensi::class);
    }
}