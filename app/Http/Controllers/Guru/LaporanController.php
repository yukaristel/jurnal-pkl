<?php

namespace App\Http\Controllers\Guru;

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
        return view('guru.laporan.index');
    }

    public function kehadiran(Request $request)
    {
        $filters = $request->all();
        $scopeIds = $this->service->getSiswaScope('guru', auth()->user())->pluck('id')->toArray();
        $filters['siswa_id'] = $filters['siswa_id'] ?? null;
        if (empty($filters['siswa_id'])) {
            $request->merge(['__scope_ids' => $scopeIds]);
        }

        $data = $this->service->dataKehadiran($filters);
        if (! empty($scopeIds) && empty($filters['siswa_id'])) {
            $data['rows'] = $data['rows']->whereIn('siswa_id', $scopeIds);
        }
        $data['scope'] = $this->service->getSiswaScope('guru', auth()->user());
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.kehadiran', $data);
            return $pdf->download('laporan-kehadiran-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('guru.laporan.kehadiran', $data);
    }

    public function jurnal(Request $request)
    {
        $filters = $request->all();
        $scopeIds = $this->service->getSiswaScope('guru', auth()->user())->pluck('id')->toArray();

        $data = $this->service->dataJurnal($filters);
        if (! empty($scopeIds) && empty($filters['siswa_id'])) {
            $data['rows'] = $data['rows']->whereIn('siswa_id', $scopeIds);
        }
        $data['scope'] = $this->service->getSiswaScope('guru', auth()->user());
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.jurnal', $data);
            return $pdf->download('laporan-jurnal-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('guru.laporan.jurnal', $data);
    }

    public function proyek(Request $request)
    {
        $filters = $request->all();
        $scopeIds = $this->service->getSiswaScope('guru', auth()->user())->pluck('id')->toArray();

        $data = $this->service->dataProyek($filters);
        if (! empty($scopeIds) && empty($filters['siswa_id'])) {
            $data['rows'] = $data['rows']->whereIn('siswa_id', $scopeIds);
        }
        $data['scope'] = $this->service->getSiswaScope('guru', auth()->user());
        $data['filters'] = $filters;

        if ($request->input('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf.proyek', $data);
            return $pdf->download('laporan-proyek-' . now()->format('Ymd-His') . '.pdf');
        }

        return view('guru.laporan.proyek', $data);
    }
}