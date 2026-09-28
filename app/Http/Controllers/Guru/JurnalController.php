<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function index(Request $request): View
    {
        $guru = $request->user()->guru;
        $siswaIds = \App\Models\PenempatanPkl::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $query = Jurnal::with(['siswa.user', 'siswa.sekolah', 'catatan.pengirim'])
            ->whereIn('siswa_id', $siswaIds);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nis', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('guru.jurnal.index', compact('jurnals'));
    }

    public function show(Request $request, Jurnal $jurnal): View
    {
        $guru = $request->user()->guru;
        $isMine = \App\Models\PenempatanPkl::where('guru_id', $guru->id)
            ->where('siswa_id', $jurnal->siswa_id)
            ->where('status', 'aktif')
            ->exists();
        abort_unless($isMine, 403);

        $jurnal->load(['siswa.user', 'siswa.sekolah', 'catatan.pengirim', 'statusLog.user']);
        return view('guru.jurnal.show', compact('jurnal'));
    }
}