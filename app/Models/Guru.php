<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = ['user_id', 'sekolah_id', 'nip', 'telepon'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penempatan()
    {
        return $this->hasMany(PenempatanPkl::class);
    }
}