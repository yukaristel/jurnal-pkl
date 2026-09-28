<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyek;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProyekController extends Controller
{
    public function index(Request $request): View
    {
        $query = Proyek::with(['siswa.user', 'siswa.sekolah', 'progress']);

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
        return view('admin.proyek.index', compact('proyeks'));
    }

    public function show(Proyek $proyek): View
    {
        $proyek->load(['siswa.user', 'siswa.sekolah', 'progress']);
        return view('admin.proyek.show', compact('proyek'));
    }

    public function destroy(Proyek $proyek)
    {
        $proyek->delete();
        return redirect()->route('admin.proyek.index')->with('success', 'Proyek dihapus.');
    }
}