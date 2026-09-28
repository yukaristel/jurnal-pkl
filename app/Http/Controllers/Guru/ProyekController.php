<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PenempatanPkl;
use App\Models\Proyek;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProyekController extends Controller
{
    public function index(Request $request): View
    {
        $guru = $request->user()->guru;
        $siswaIds = PenempatanPkl::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $query = Proyek::with(['siswa.user', 'siswa.sekolah'])
            ->whereIn('siswa_id', $siswaIds);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nis', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")));
            });
        }

        $proyeks = $query->orderByDesc('id')->paginate(20)->withQueryString();
        return view('guru.proyek.index', compact('proyeks'));
    }

    public function show(Request $request, Proyek $proyek): View
    {
        $guru = $request->user()->guru;
        $isMine = PenempatanPkl::where('guru_id', $guru->id)
            ->where('siswa_id', $proyek->siswa_id)
            ->where('status', 'aktif')
            ->exists();
        abort_unless($isMine, 403);

        $proyek->load(['siswa.user', 'progress']);
        return view('guru.proyek.show', compact('proyek'));
    }
}