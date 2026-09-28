<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PenempatanPkl;
use App\Models\Proyek;
use App\Models\Siswa;
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
        $pembimbing = $request->user()->pembimbing;
        $siswaIds = PenempatanPkl::where('pembimbing_id', $pembimbing->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $query = Proyek::with(['siswa.user'])
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
        return view('pembimbing.proyek.index', compact('proyeks'));
    }

    public function create(Request $request): View
    {
        $pembimbing = $request->user()->pembimbing;
        $siswa = Siswa::with('user')
            ->whereIn('id', PenempatanPkl::where('pembimbing_id', $pembimbing->id)->where('status', 'aktif')->pluck('siswa_id'))
            ->orderBy('nis')->get();
        return view('pembimbing.proyek.create', compact('siswa'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'tanggal_mulai'   => 'required|date',
            'target_selesai'  => 'required|date|after_or_equal:tanggal_mulai',
            'progress_awal.keterangan' => 'nullable|string',
            'progress_awal.persen'     => 'nullable|integer|min:0|max:100',
        ]);

        $this->authorize($request, $data['siswa_id']);

        try {
            $proyek = $this->proyekService->create(
                Siswa::find($data['siswa_id']),
                $data,
                $request->user()->id
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('pembimbing.proyek.show', $proyek)->with('success', 'Proyek berhasil diberikan.');
    }

    public function show(Request $request, Proyek $proyek): View
    {
        $this->authorize($request, $proyek->siswa_id);
        $proyek->load(['siswa.user', 'progress']);
        return view('pembimbing.proyek.show', compact('proyek'));
    }

    public function edit(Request $request, Proyek $proyek): View
    {
        $this->authorize($request, $proyek->siswa_id);
        return view('pembimbing.proyek.edit', compact('proyek'));
    }

    public function update(Request $request, Proyek $proyek)
    {
        $this->authorize($request, $proyek->siswa_id);

        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'tanggal_mulai'  => 'required|date',
            'target_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $this->proyekService->update($proyek, $data);
        return redirect()->route('pembimbing.proyek.show', $proyek)->with('success', 'Proyek diperbarui.');
    }

    public function destroy(Request $request, Proyek $proyek)
    {
        $this->authorize($request, $proyek->siswa_id);
        $proyek->delete();
        return redirect()->route('pembimbing.proyek.index')->with('success', 'Proyek dihapus.');
    }

    public function addProgress(Request $request, Proyek $proyek)
    {
        $this->authorize($request, $proyek->siswa_id);

        $data = $request->validate([
            'keterangan' => 'required|string|max:2000',
            'persen'     => 'required|integer|min:0|max:100',
            'lampiran'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('proyek-lampiran', 'public');
        }

        $this->proyekService->addProgress($proyek, array_merge($data, ['lampiran' => $lampiranPath]), $request->user()->id);

        return back()->with('success', 'Progress proyek diperbarui.');
    }

    public function setStatus(Request $request, Proyek $proyek)
    {
        $this->authorize($request, $proyek->siswa_id);

        $data = $request->validate([
            'status' => 'required|in:belum_mulai,berjalan,selesai,batal',
            'hasil'  => 'nullable|string',
        ]);

        try {
            $this->proyekService->setStatus($proyek, $data['status'], $data['hasil'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status proyek diubah.');
    }

    private function authorize(Request $request, int $siswaId): void
    {
        $pembimbing = $request->user()->pembimbing;
        $isMine = PenempatanPkl::where('pembimbing_id', $pembimbing->id)
            ->where('siswa_id', $siswaId)
            ->where('status', 'aktif')
            ->exists();
        abort_unless($isMine, 403);
    }
}