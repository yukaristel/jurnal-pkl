<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    protected $table = 'pembimbing';

    protected $fillable = ['user_id', 'perusahaan_id', 'jabatan', 'telepon'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function penempatan()
    {
        return $this->hasMany(PenempatanPkl::class);
    }

    public function qrTokens()
    {
        return $this->hasMany(QrToken::class);
    }
}