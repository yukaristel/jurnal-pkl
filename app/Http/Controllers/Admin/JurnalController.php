<?php

namespace App\Http\Controllers\Admin;

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
        $query = Jurnal::with(['siswa.user', 'siswa.sekolah']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nis', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.jurnal.index', compact('jurnals'));
    }

    public function show(Jurnal $jurnal): View
    {
        $jurnal->load(['siswa.user', 'siswa.sekolah', 'catatan.pengirim', 'statusLog.user', 'unlockedByUser']);
        return view('admin.jurnal.show', compact('jurnal'));
    }

    public function unlock(Jurnal $jurnal)
    {
        if ($jurnal->status !== 'done') {
            return back()->with('error', 'Hanya jurnal berstatus done yang bisa dibuka kunci.');
        }
        $this->jurnalService->unlock($jurnal, auth()->id());
        return back()->with('success', 'Jurnal #' . $jurnal->id . ' berhasil dibuka kuncinya. Siswa dapat mengedit.');
    }
}