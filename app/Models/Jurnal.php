<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    protected $fillable = [
        'siswa_id', 'penempatan_id', 'absensi_id', 'parent_id', 'tanggal',
        'judul', 'deskripsi', 'kesulitan', 'status',
        'hold_at', 'done_at', 'unlocked_by', 'unlocked_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'hold_at' => 'datetime',
        'done_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function penempatan()
    {
        return $this->belongsTo(PenempatanPkl::class);
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function parent()
    {
        return $this->belongsTo(Jurnal::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Jurnal::class, 'parent_id');
    }

    public function catatan()
    {
        return $this->hasMany(Catatan::class);
    }

    public function lampiran()
    {
        return $this->hasMany(JurnalLampiran::class);
    }

    public function statusLog()
    {
        return $this->hasMany(JurnalStatusLog::class);
    }

    public function isLocked(): bool
    {
        return $this->status === 'done' && $this->unlocked_by === null;
    }

    public function unlockedByUser()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }
}