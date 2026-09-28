<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanAbsensi;
use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PengaturanAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan = Perusahaan::orderBy('nama')->get();
        $selectedId = $request->input('perusahaan_id') ?? $perusahaan->first()?->id;

        $pengaturan = [];
        if ($selectedId) {
            $rows = PengaturanAbsensi::where('perusahaan_id', $selectedId)->get()->keyBy('hari');
            for ($i = 1; $i <= 7; $i++) {
                $pengaturan[$i] = $rows->get($i);
            }
        }

        return view('admin.pengaturan-absensi.index', compact('perusahaan', 'pengaturan', 'selectedId'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'settings' => 'required|array',
            'settings.*.hari' => 'required|integer|between:1,7',
            'settings.*.jam_masuk' => 'nullable|date_format:H:i',
            'settings.*.batas_terlambat' => 'nullable|date_format:H:i',
            'settings.*.jam_pulang_min' => 'nullable|date_format:H:i',
            'settings.*.libur' => 'nullable|boolean',
        ]);

        foreach ($data['settings'] as $row) {
            PengaturanAbsensi::updateOrCreate(
                ['perusahaan_id' => $data['perusahaan_id'], 'hari' => $row['hari']],
                [
                    'jam_masuk'       => $row['jam_masuk'] ?? null,
                    'batas_terlambat' => $row['batas_terlambat'] ?? null,
                    'jam_pulang_min'  => $row['jam_pulang_min'] ?? null,
                    'libur'           => (bool) ($row['libur'] ?? false),
                ]
            );
        }

        return back()->with('success', 'Pengaturan absensi berhasil disimpan.');
    }
}