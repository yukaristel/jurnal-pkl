<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function __construct(private AbsensiService $absensi)
    {
    }

    public function scan(): View
    {
        return view('siswa.absensi.scan');
    }

    public function submitScan(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $result = $this->absensi->scanMasuk($data['token']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function pulang(Request $request)
    {
        $result = $this->absensi->pulang();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function izinForm(): View
    {
        $siswa = auth()->user()->siswa;
        $absensiHariIni = $siswa?->absensi()->where('tanggal', today())->first();
        return view('siswa.absensi.izin', compact('absensiHariIni'));
    }

    public function submitIzin(Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required|in:izin,sakit',
            'keterangan' => 'nullable|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('absensi-lampiran', 'public');
        }

        $result = $this->absensi->ajukanIzin($data['jenis'], $data['keterangan'] ?? null, $lampiranPath);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}