<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
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
        return view('admin.laporan.index');
    }

    public function kehadiran(Request $request)
    {
        $data = $this->service->dataKehadiran($request->all());
        $data['scope'] = $this->service->getSiswaScope('superadmin', auth()->user());
        $data['perusahaan'] = Perusahaan::orderBy('nama')->get();
        $data['filters'] = $request->all();

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.kehadiran', $data);
            return $pdf->download('laporan-kehadiran-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('admin.laporan.kehadiran', $data);
    }

    public function jurnal(Request $request)
    {
        $data = $this->service->dataJurnal($request->all());
        $data['scope'] = $this->service->getSiswaScope('superadmin', auth()->user());
        $data['filters'] = $request->all();

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.jurnal', $data);
            return $pdf->download('laporan-jurnal-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('admin.laporan.jurnal', $data);
    }

    public function proyek(Request $request)
    {
        $data = $this->service->dataProyek($request->all());
        $data['scope'] = $this->service->getSiswaScope('superadmin', auth()->user());
        $data['filters'] = $request->all();

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.proyek', $data);
            return $pdf->download('laporan-proyek-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('admin.laporan.proyek', $data);
    }
}