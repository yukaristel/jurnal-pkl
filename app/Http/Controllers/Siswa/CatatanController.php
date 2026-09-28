<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Catatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatatanController extends Controller
{
    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa;

        $catatanJurnal = Catatan::with(['jurnal', 'pengirim'])
            ->where('siswa_id', $siswa->id)
            ->where('tipe', 'catatan_jurnal')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'catatan_page');

        $pesan = Catatan::with(['pengirim'])
            ->where('siswa_id', $siswa->id)
            ->where('tipe', 'pesan')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'pesan_page');

        return view('siswa.catatan.index', compact('catatanJurnal', 'pesan'));
    }

    public function show(Catatan $catatan): View
    {
        abort_unless($catatan->siswa_id === auth()->user()->siswa?->id, 403);

        if (! $catatan->dibaca_at) {
            $catatan->update(['dibaca_at' => now()]);
        }

        $catatan->load(['jurnal', 'pengirim']);

        return view('siswa.catatan.show', compact('catatan'));
    }
}