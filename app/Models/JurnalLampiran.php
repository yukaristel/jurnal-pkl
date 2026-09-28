<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalLampiran extends Model
{
    protected $table = 'jurnal_lampiran';

    protected $fillable = ['jurnal_id', 'path', 'tipe'];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }
}