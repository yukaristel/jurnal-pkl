<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanAbsensi extends Model
{
    protected $table = 'pengaturan_absensi';

    protected $fillable = [
        'perusahaan_id', 'hari', 'jam_masuk', 'batas_terlambat', 'jam_pulang_min', 'libur',
    ];

    protected $casts = [
        'libur' => 'boolean',
        'hari' => 'integer',
    ];

    public const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public static function forHari(int $perusahaanId, int $hari): ?self
    {
        return static::where('perusahaan_id', $perusahaanId)
            ->where('hari', $hari)
            ->first();
    }
}