<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(private LaporanService $service)
    {
    }

    public function index(): View
    {
        return view('siswa.laporan.index');
    }

    public function kehadiran(Request $request)
    {
        $filters = $request->all();
        $filters['siswa_id'] = auth()->user()->siswa->id;

        $data = $this->service->dataKehadiran($filters);
        $data['scope'] = collect([auth()->user()->siswa]);
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.kehadiran', $data);
            return $pdf->download('laporan-kehadiran-saya-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('siswa.laporan.kehadiran', $data);
    }

    public function jurnal(Request $request)
    {
        $filters = $request->all();
        $filters['siswa_id'] = auth()->user()->siswa->id;

        $data = $this->service->dataJurnal($filters);
        $data['scope'] = collect([auth()->user()->siswa]);
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.jurnal', $data);
            return $pdf->download('laporan-jurnal-saya-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('siswa.laporan.jurnal', $data);
    }

    public function proyek(Request $request)
    {
        $filters = $request->all();
        $filters['siswa_id'] = auth()->user()->siswa->id;

        $data = $this->service->dataProyek($filters);
        $data['scope'] = collect([auth()->user()->siswa]);
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.proyek', $data);
            return $pdf->download('laporan-proyek-saya-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('siswa.laporan.proyek', $data);
    }
}