<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Pembimbing;
use App\Models\PenempatanPkl;
use App\Models\Perusahaan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenempatanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PenempatanPkl::with(['siswa.user', 'perusahaan', 'pembimbing.user', 'guru.user']);
            $totalRecords = $query->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%")
                      ->orWhereHas('siswa', fn($s) => $s->where('nis', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")))
                      ->orWhereHas('perusahaan', fn($p) => $p->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('pembimbing', fn($pb) => $pb->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")))
                      ->orWhereHas('guru', fn($g) => $g->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")));
                });
            }
            $filteredRecords = $query->count();

            $rows = $query->orderBy('id', 'desc')
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            $data = $rows->map(function ($row) {
                $periode = \Carbon\Carbon::parse($row->tanggal_mulai)->format('d M Y') . ' → ' . \Carbon\Carbon::parse($row->tanggal_selesai)->format('d M Y');
                $colors = ['aktif' => 'success', 'selesai' => 'secondary', 'batal' => 'danger'];
                $color = $colors[$row->status] ?? 'secondary';
                $edit = '<a href="'.route('admin.penempatan.edit', $row->id).'" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                $hapus = '<button class="btn btn-sm btn-danger btn-delete ms-1" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                return [
                    'id' => $row->id,
                    'siswa' => $row->siswa->user->name ?? '-',
                    'nis' => $row->siswa->nis ?? '-',
                    'perusahaan' => $row->perusahaan->nama ?? '-',
                    'pembimbing' => $row->pembimbing->user->name ?? '-',
                    'guru' => $row->guru->user->name ?? '-',
                    'periode' => $periode,
                    'status_badge' => '<span class="badge bg-'.$color.'">'.ucfirst($row->status).'</span>',
                    'aksi' => '<div class="d-flex">'.$edit.$hapus.'</div>',
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        }

        return view('admin.penempatan.index');
    }

    public function create()
    {
        return view('admin.penempatan.create', [
            'siswa'      => Siswa::orderBy('nis')->with('user')->get(),
            'perusahaan' => Perusahaan::orderBy('nama')->get(),
            'pembimbing' => Pembimbing::with(['user', 'perusahaan'])->get(),
            'guru'       => Guru::with(['user', 'sekolah'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (PenempatanPkl::where('siswa_id', $data['siswa_id'])->where('status', 'aktif')->exists()) {
            return back()->withInput()->withErrors(['siswa_id' => 'Siswa sudah punya penempatan aktif.']);
        }

        PenempatanPkl::create($data);
        return redirect()->route('admin.penempatan.index')->with('success', 'Penempatan PKL berhasil ditambahkan.');
    }

    public function edit(PenempatanPkl $penempatan)
    {
        return view('admin.penempatan.edit', [
            'penempatan' => $penempatan,
            'siswa'      => Siswa::orderBy('nis')->with('user')->get(),
            'perusahaan' => Perusahaan::orderBy('nama')->get(),
            'pembimbing' => Pembimbing::with(['user', 'perusahaan'])->get(),
            'guru'       => Guru::with(['user', 'sekolah'])->get(),
        ]);
    }

    public function update(Request $request, PenempatanPkl $penempatan)
    {
        $data = $this->validated($request);

        if ($data['status'] === 'aktif') {
            $exists = PenempatanPkl::where('siswa_id', $data['siswa_id'])
                ->where('status', 'aktif')
                ->where('id', '!=', $penempatan->id)
                ->exists();
            if ($exists) {
                return back()->withInput()->withErrors(['siswa_id' => 'Siswa sudah punya penempatan aktif lain.']);
            }
        }

        $penempatan->update($data);
        return redirect()->route('admin.penempatan.index')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function destroy(PenempatanPkl $penempatan)
    {
        $penempatan->delete();
        return redirect()->route('admin.penempatan.index')->with('success', 'Penempatan PKL berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'siswa_id'      => 'required|exists:siswa,id',
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'pembimbing_id' => 'required|exists:pembimbing,id',
            'guru_id'       => 'required|exists:guru,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status'        => ['required', Rule::in(['aktif', 'selesai', 'batal'])],
        ]);
    }
}