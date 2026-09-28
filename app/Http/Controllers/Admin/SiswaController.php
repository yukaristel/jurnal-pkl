<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Siswa::with(['sekolah', 'user']);
            $totalRecords = $query->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nis', 'like', "%{$search}%")
                      ->orWhere('kelas', 'like', "%{$search}%")
                      ->orWhere('jurusan', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                      ->orWhereHas('sekolah', fn($s) => $s->where('nama', 'like', "%{$search}%"));
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
                    'nis' => $row->nis,
                    'name' => $row->user->name ?? '-',
                    'kelas' => $row->kelas,
                    'jurusan' => $row->jurusan,
                    'sekolah' => $row->sekolah->nama ?? '-',
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

        $sekolah = Sekolah::orderBy('nama')->get();
        return view('admin.siswa.index', compact('sekolah'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'sekolah_id' => 'required|exists:sekolah,id',
            'nis'       => 'required|string|max:255|unique:siswa,nis',
            'kelas'     => 'nullable|string|max:255',
            'jurusan'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'siswa',
                'is_active' => true,
            ]);
            Siswa::create([
                'user_id'    => $user->id,
                'sekolah_id' => $data['sekolah_id'],
                'nis'        => $data['nis'],
                'kelas'      => $data['kelas'] ?? null,
                'jurusan'    => $data['jurusan'] ?? null,
                'telepon'    => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $siswa->user_id,
            'email'     => 'required|email|max:255|unique:users,email,' . $siswa->user_id,
            'password'  => 'nullable|string|min:6',
            'sekolah_id' => 'required|exists:sekolah,id',
            'nis'       => 'required|string|max:255|unique:siswa,nis,' . $siswa->id,
            'kelas'     => 'nullable|string|max:255',
            'jurusan'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $siswa) {
            $user = $siswa->user;
            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            $siswa->update([
                'sekolah_id' => $data['sekolah_id'],
                'nis'        => $data['nis'],
                'kelas'      => $data['kelas'] ?? null,
                'jurusan'    => $data['jurusan'] ?? null,
                'telepon'    => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {
            $user = $siswa->user;
            $siswa->delete();
            $user->delete();
        });
        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function editJson(Siswa $siswa)
    {
        $siswa->load('user');
        return response()->json($siswa);
    }
}