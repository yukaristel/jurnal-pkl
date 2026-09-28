<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyekProgress extends Model
{
    protected $table = 'proyek_progress';

    protected $fillable = ['proyek_id', 'keterangan', 'persen', 'lampiran'];

    public function proyek()
    {
        return $this->belongsTo(Proyek::class);
    }
}