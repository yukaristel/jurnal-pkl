<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rows = Sekolah::query();
            $totalRecords = $rows->count();
            $filteredRecords = $totalRecords;

            if ($search = $request->input('search.value')) {
                $rows->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('npsn', 'like', "%{$search}%")
                      ->orWhere('telepon', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%");
                });
                $filteredRecords = $rows->count();
            }

            $rows = $rows->orderBy('id', 'asc')
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            $data = $rows->map(function ($row) {
                $edit = '<button class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button>';
                $hapus = '<button class="btn btn-sm btn-danger btn-delete ms-1" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                return [
                    'id' => $row->id,
                    'nama' => $row->nama,
                    'npsn' => $row->npsn,
                    'telepon' => $row->telepon,
                    'alamat' => $row->alamat,
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

        return view('admin.sekolah.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:255',
            'npsn'    => 'required|string|max:255|unique:sekolah,npsn',
            'alamat'  => 'nullable|string',
            'telepon' => 'nullable|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('sekolah', 'public');
        }

        Sekolah::create($data);
        return redirect()->route('admin.sekolah.index')->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:255',
            'npsn'    => 'required|string|max:255|unique:sekolah,npsn,' . $sekolah->id,
            'alamat'  => 'nullable|string',
            'telepon' => 'nullable|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('sekolah', 'public');
        }

        $sekolah->update($data);
        return redirect()->route('admin.sekolah.index')->with('success', 'Sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();
        return redirect()->route('admin.sekolah.index')->with('success', 'Sekolah berhasil dihapus.');
    }

    public function editJson(Sekolah $sekolah)
    {
        return response()->json($sekolah);
    }
}