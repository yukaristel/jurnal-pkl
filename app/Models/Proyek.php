<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyek extends Model
{
    protected $table = 'proyek';

    protected $fillable = [
        'siswa_id', 'penempatan_id', 'judul', 'deskripsi', 'diberikan_oleh',
        'tanggal_mulai', 'target_selesai', 'tanggal_selesai', 'status', 'hasil',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'target_selesai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function penempatan()
    {
        return $this->belongsTo(PenempatanPkl::class);
    }

    public function pemberi()
    {
        return $this->belongsTo(User::class, 'diberikan_oleh');
    }

    public function progress()
    {
        return $this->hasMany(ProyekProgress::class);
    }
}