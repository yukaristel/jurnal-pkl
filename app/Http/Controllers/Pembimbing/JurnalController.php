<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function index(Request $request): View
    {
        $pembimbing = $request->user()->pembimbing;
        $siswaIds = \App\Models\PenempatanPkl::where('pembimbing_id', $pembimbing->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $query = Jurnal::with(['siswa.user', 'catatan.pengirim'])
            ->whereIn('siswa_id', $siswaIds);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nis', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('pembimbing.jurnal.index', compact('jurnals'));
    }

    public function show(Request $request, Jurnal $jurnal): View
    {
        $pembimbing = $request->user()->pembimbing;
        $isMine = \App\Models\PenempatanPkl::where('pembimbing_id', $pembimbing->id)
            ->where('siswa_id', $jurnal->siswa_id)
            ->where('status', 'aktif')
            ->exists();
        abort_unless($isMine, 403);

        $jurnal->load(['siswa.user', 'catatan.pengirim', 'statusLog.user', 'children']);
        return view('pembimbing.jurnal.show', compact('jurnal'));
    }
}