<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Guru::with(['sekolah', 'user']);
            $totalRecords = $query->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
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
                    'nip' => $row->nip,
                    'name' => $row->user->name ?? '-',
                    'email' => $row->user->email ?? '-',
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
        return view('admin.guru.index', compact('sekolah'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'sekolah_id' => 'required|exists:sekolah,id',
            'nip'       => 'required|string|max:255|unique:guru,nip',
            'telepon'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'guru',
                'is_active' => true,
            ]);
            Guru::create([
                'user_id'    => $user->id,
                'sekolah_id' => $data['sekolah_id'],
                'nip'        => $data['nip'],
                'telepon'    => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function update(Request $request, Guru $guru)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $guru->user_id,
            'email'     => 'required|email|max:255|unique:users,email,' . $guru->user_id,
            'password'  => 'nullable|string|min:6',
            'sekolah_id' => 'required|exists:sekolah,id',
            'nip'       => 'required|string|max:255|unique:guru,nip,' . $guru->id,
            'telepon'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $guru) {
            $user = $guru->user;
            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            $guru->update([
                'sekolah_id' => $data['sekolah_id'],
                'nip'        => $data['nip'],
                'telepon'    => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        DB::transaction(function () use ($guru) {
            $user = $guru->user;
            $guru->delete();
            $user->delete();
        });
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus.');
    }

    public function editJson(Guru $guru)
    {
        $guru->load('user');
        return response()->json($guru);
    }
}