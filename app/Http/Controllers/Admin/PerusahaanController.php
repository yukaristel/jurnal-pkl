<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Perusahaan::query();
            $totalRecords = $query->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('bidang_usaha', 'like', "%{$search}%")
                      ->orWhere('telepon', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%");
                });
            }
            $filteredRecords = $query->count();

            $rows = $query->orderBy('id', 'asc')
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            $data = $rows->map(function ($row) {
                $edit = '<button class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button>';
                $hapus = '<button class="btn btn-sm btn-danger btn-delete ms-1" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                return [
                    'id' => $row->id,
                    'nama' => $row->nama,
                    'bidang_usaha' => $row->bidang_usaha,
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

        return view('admin.perusahaan.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:255',
            'alamat'        => 'nullable|string',
            'bidang_usaha'  => 'nullable|string|max:255',
            'telepon'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'radius_meter'  => 'nullable|integer|min:0',
        ]);

        Perusahaan::create($data);
        return redirect()->route('admin.perusahaan.index')->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function update(Request $request, Perusahaan $perusahaan)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:255',
            'alamat'        => 'nullable|string',
            'bidang_usaha'  => 'nullable|string|max:255',
            'telepon'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'radius_meter'  => 'nullable|integer|min:0',
        ]);

        $perusahaan->update($data);
        return redirect()->route('admin.perusahaan.index')->with('success', 'Perusahaan berhasil diperbarui.');
    }

    public function destroy(Perusahaan $perusahaan)
    {
        $perusahaan->delete();
        return redirect()->route('admin.perusahaan.index')->with('success', 'Perusahaan berhasil dihapus.');
    }

    public function editJson(Perusahaan $perusahaan)
    {
        return response()->json($perusahaan);
    }
}