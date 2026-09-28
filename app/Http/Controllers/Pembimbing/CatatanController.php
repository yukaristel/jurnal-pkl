<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Catatan;
use App\Models\Jurnal;
use App\Models\PenempatanPkl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatatanController extends Controller
{
    public function create(Request $request, Jurnal $jurnal): View
    {
        $this->authorize($request, $jurnal);
        return view('pembimbing.catatan.create', compact('jurnal'));
    }

    public function store(Request $request, Jurnal $jurnal)
    {
        $this->authorize($request, $jurnal);

        $data = $request->validate([
            'isi' => 'required|string|max:2000',
        ]);

        Catatan::create([
            'jurnal_id'   => $jurnal->id,
            'siswa_id'    => $jurnal->siswa_id,
            'pengirim_id' => $request->user()->id,
            'tipe'        => 'catatan_jurnal',
            'isi'         => $data['isi'],
        ]);

        return redirect()->route('pembimbing.jurnal.show', $jurnal)->with('success', 'Catatan terkirim.');
    }

    private function authorize(Request $request, Jurnal $jurnal): void
    {
        $pembimbing = $request->user()->pembimbing;
        $isMine = PenempatanPkl::where('pembimbing_id', $pembimbing->id)
            ->where('siswa_id', $jurnal->siswa_id)
            ->where('status', 'aktif')
            ->exists();
        abort_unless($isMine, 403);
    }
}