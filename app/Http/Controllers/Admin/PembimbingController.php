<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PembimbingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Pembimbing::with(['perusahaan', 'user']);
            $totalRecords = $query->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('jabatan', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                      ->orWhereHas('perusahaan', fn($p) => $p->where('nama', 'like', "%{$search}%"));
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
                    'name' => $row->user->name ?? '-',
                    'email' => $row->user->email ?? '-',
                    'jabatan' => $row->jabatan,
                    'perusahaan' => $row->perusahaan->nama ?? '-',
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

        $perusahaan = Perusahaan::orderBy('nama')->get();
        return view('admin.pembimbing.index', compact('perusahaan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:6',
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'jabatan'      => 'nullable|string|max:255',
            'telepon'      => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'pembimbing',
                'is_active' => true,
            ]);
            Pembimbing::create([
                'user_id'       => $user->id,
                'perusahaan_id' => $data['perusahaan_id'],
                'jabatan'       => $data['jabatan'] ?? null,
                'telepon'       => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.pembimbing.index')->with('success', 'Pembimbing berhasil ditambahkan.');
    }

    public function update(Request $request, Pembimbing $pembimbing)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,' . $pembimbing->user_id,
            'email'        => 'required|email|max:255|unique:users,email,' . $pembimbing->user_id,
            'password'     => 'nullable|string|min:6',
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'jabatan'      => 'nullable|string|max:255',
            'telepon'      => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $pembimbing) {
            $user = $pembimbing->user;
            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            $pembimbing->update([
                'perusahaan_id' => $data['perusahaan_id'],
                'jabatan'       => $data['jabatan'] ?? null,
                'telepon'       => $data['telepon'] ?? null,
            ]);
        });

        return redirect()->route('admin.pembimbing.index')->with('success', 'Pembimbing berhasil diperbarui.');
    }

    public function destroy(Pembimbing $pembimbing)
    {
        DB::transaction(function () use ($pembimbing) {
            $user = $pembimbing->user;
            $pembimbing->delete();
            $user->delete();
        });
        return redirect()->route('admin.pembimbing.index')->with('success', 'Pembimbing berhasil dihapus.');
    }

    public function editJson(Pembimbing $pembimbing)
    {
        $pembimbing->load('user');
        return response()->json($pembimbing);
    }
}