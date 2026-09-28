<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Catatan;
use App\Models\PenempatanPkl;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesanController extends Controller
{
    public function index(Request $request): View
    {
        $guru = $request->user()->guru;
        $siswaIds = PenempatanPkl::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $query = Catatan::with(['siswa.user', 'pengirim'])
            ->where('pengirim_id', $request->user()->id)
            ->where('tipe', 'pesan')
            ->whereIn('siswa_id', $siswaIds);

        if ($search = $request->input('q')) {
            $query->where('isi', 'like', "%{$search}%");
        }

        $pesan = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $siswa = Siswa::with('user')->whereIn('id', $siswaIds)->orderBy('nis')->get();

        return view('guru.pesan.index', compact('pesan', 'siswa'));
    }

    public function store(Request $request)
    {
        $guru = $request->user()->guru;
        $siswaIds = PenempatanPkl::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'isi'      => 'required|string|max:2000',
        ]);

        if (! $siswaIds->contains($data['siswa_id'])) {
            return back()->withErrors(['siswa_id' => 'Siswa bukan bimbingan Anda.']);
        }

        Catatan::create([
            'jurnal_id'   => null,
            'siswa_id'    => $data['siswa_id'],
            'pengirim_id' => $request->user()->id,
            'tipe'        => 'pesan',
            'isi'         => $data['isi'],
        ]);

        return back()->with('success', 'Pesan terkirim.');
    }
}