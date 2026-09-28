<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa;

        return view('siswa.dashboard', [
            'siswa' => $siswa,
            'absensiHariIni' => $siswa?->absensi()->whereDate('tanggal', today())->first(),
            'penempatanAktif' => $siswa?->penempatanAktif,
        ]);
    }
}