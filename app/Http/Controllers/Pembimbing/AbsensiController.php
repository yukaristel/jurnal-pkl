<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function __construct(private AbsensiService $absensi)
    {
    }

    public function qr(Request $request): View
    {
        $pembimbing = $request->user()->pembimbing;
        $token = $this->absensi->getOrCreateToken($pembimbing);
        $siswaAbsenHariIni = \App\Models\Absensi::whereHas('penempatan', function ($q) use ($pembimbing) {
                $q->where('pembimbing_id', $pembimbing->id);
            })
            ->where('tanggal', today())
            ->with('siswa.user')
            ->get();

        return view('pembimbing.absensi.qr', [
            'token' => $token,
            'pembimbing' => $pembimbing,
            'remaining' => $this->absensi->remainingSeconds($token),
            'siswaAbsenHariIni' => $siswaAbsenHariIni,
        ]);
    }

    public function tokenJson(Request $request)
    {
        $pembimbing = $request->user()->pembimbing;
        $token = $this->absensi->getOrCreateToken($pembimbing);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($token->token);

        return response()->json([
            'token' => $token->token,
            'qr_svg' => (string) $qrSvg,
            'expired_at' => $token->expired_at->toIso8601String(),
            'remaining' => $this->absensi->remainingSeconds($token),
        ]);
    }
}