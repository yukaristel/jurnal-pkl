<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Proyek;
use App\Services\ProyekService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProyekController extends Controller
{
    public function __construct(private ProyekService $proyekService)
    {
    }

    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa;
        $proyeks = Proyek::with('progress')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('siswa.proyek.index', compact('proyeks'));
    }

    public function show(Request $request, Proyek $proyek): View
    {
        abort_unless($proyek->siswa_id === $request->user()->siswa?->id, 403);
        $proyek->load('progress');
        return view('siswa.proyek.show', compact('proyek'));
    }

    public function addProgress(Request $request, Proyek $proyek)
    {
        abort_unless($proyek->siswa_id === $request->user()->siswa?->id, 403);

        $data = $request->validate([
            'keterangan' => 'required|string|max:2000',
            'persen'     => 'required|integer|min:0|max:100',
            'lampiran'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('proyek-lampiran', 'public');
        }

        $this->proyekService->addProgress(
            $proyek,
            array_merge($data, ['lampiran' => $lampiranPath]),
            $request->user()->id
        );

        return back()->with('success', 'Progress berhasil dicatat.');
    }
}