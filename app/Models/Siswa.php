<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = ['user_id', 'sekolah_id', 'nis', 'kelas', 'jurusan', 'telepon'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penempatanAktif()
    {
        return $this->hasOne(PenempatanPkl::class)->where('status', 'aktif');
    }

    public function penempatan()
    {
        return $this->hasMany(PenempatanPkl::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }
}