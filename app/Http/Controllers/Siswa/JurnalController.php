<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use App\Services\JurnalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function __construct(private JurnalService $jurnalService)
    {
    }

    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa;
        $jurnals = Jurnal::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->orderByDesc('id')
            ->get();

        $canCreate = $this->jurnalService->canCreate($siswa);
        $absensiHariIni = $siswa->absensi()->where('tanggal', today())->first();

        return view('siswa.jurnal.index', compact('jurnals', 'canCreate', 'absensiHariIni'));
    }

    public function create(): View
    {
        $siswa = auth()->user()->siswa;
        if (! $this->jurnalService->canCreate($siswa)) {
            return redirect()->route('siswa.jurnal.index')->with('error', 'Anda harus absen masuk dulu sebelum membuat jurnal.');
        }
        return view('siswa.jurnal.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kesulitan' => 'nullable|string',
        ]);

        try {
            $jurnal = $this->jurnalService->create(auth()->user()->siswa, $data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('siswa.jurnal.show', $jurnal)->with('success', 'Jurnal berhasil dibuat.');
    }

    public function show(Jurnal $jurnal): View
    {
        $this->authorizeSiswa($jurnal);
        $jurnal->load(['catatan.pengirim', 'children', 'statusLog.user']);
        $jurnal->catatan->whereNull('dibaca_at')->each->update(['dibaca_at' => now()]);
        return view('siswa.jurnal.show', compact('jurnal'));
    }

    public function edit(Jurnal $jurnal): \Illuminate\Http\Response|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeSiswa($jurnal);
        if ($jurnal->status === 'done' && $jurnal->unlocked_by === null) {
            return redirect()->route('siswa.jurnal.show', $jurnal)->with('error', 'Jurnal sudah selesai dan dikunci.');
        }
        return view('siswa.jurnal.edit', compact('jurnal'));
    }

    public function update(Request $request, Jurnal $jurnal)
    {
        $this->authorizeSiswa($jurnal);

        $data = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kesulitan' => 'nullable|string',
        ]);

        try {
            $this->jurnalService->update($jurnal, $data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('siswa.jurnal.show', $jurnal)->with('success', 'Jurnal diperbarui.');
    }

    public function markDone(Jurnal $jurnal)
    {
        $this->authorizeSiswa($jurnal);
        if ($jurnal->status !== 'on_progress') {
            return back()->with('error', 'Jurnal harus berstatus on_progress untuk ditandai selesai.');
        }
        $this->jurnalService->markDone($jurnal);
        return back()->with('success', 'Jurnal ditandai selesai (terkunci).');
    }

    public function hold(Jurnal $jurnal)
    {
        $this->authorizeSiswa($jurnal);
        if ($jurnal->status !== 'on_progress') {
            return back()->with('error', 'Jurnal harus berstatus on_progress untuk di-hold.');
        }
        $this->jurnalService->hold($jurnal);
        return back()->with('success', 'Jurnal ditahan.');
    }

    public function reprogress(Jurnal $jurnal)
    {
        $this->authorizeSiswa($jurnal);
        try {
            $new = $this->jurnalService->reprogress($jurnal);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('siswa.jurnal.show', $new)->with('success', 'Jurnal dibuka kembali.');
    }

    private function authorizeSiswa(Jurnal $jurnal): void
    {
        if ($jurnal->siswa_id !== auth()->user()->siswa?->id) {
            abort(403);
        }
    }
}