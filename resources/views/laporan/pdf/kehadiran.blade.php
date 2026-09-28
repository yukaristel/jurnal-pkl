<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Kehadiran</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
h1 { font-size: 16px; margin: 0; }
.meta { font-size: 10px; color: #666; margin-bottom: 12px; }
.summary { background: #f3f4f6; padding: 8px; border-radius: 4px; margin-bottom: 12px; }
.summary span { display: inline-block; margin-right: 14px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ccc; padding: 5px; vertical-align: top; }
th { background: #1e293b; color: #fff; font-size: 10px; }
tr:nth-child(even) td { background: #f9fafb; }
.badge { padding: 1px 5px; border-radius: 3px; font-size: 9px; color: #fff; }
.s-hadir { background: #16a34a; } .s-terlambat { background: #d97706; }
.s-izin { background: #2563eb; } .s-sakit { background: #7c3aed; } .s-alpa { background: #dc2626; }
.footer { margin-top: 18px; font-size: 9px; color: #666; text-align: center; }
</style></head>
<body>
<h1>Laporan Kehadiran PKL</h1>
<div class="meta">
    Dicetak: {{ now()->format('d M Y H:i') }} WIB
    @if(!empty($filters['dari']) && !empty($filters['sampai']))
        | Periode: {{ \Carbon\Carbon::parse($filters['dari'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['sampai'])->format('d M Y') }}
    @endif
</div>
<div class="summary">
    <span><strong>Total:</strong> {{ $summary['total'] }}</span>
    <span><strong>Hadir:</strong> {{ $summary['hadir'] }}</span>
    <span><strong>Terlambat:</strong> {{ $summary['terlambat'] }}</span>
    <span><strong>Izin:</strong> {{ $summary['izin'] }}</span>
    <span><strong>Sakit:</strong> {{ $summary['sakit'] }}</span>
    <span><strong>Alpa:</strong> {{ $summary['alpa'] }}</span>
</div>
<table>
    <thead><tr>
        <th style="width:18px;">#</th>
        <th>Tanggal</th>
        <th>Siswa</th>
        <th>Perusahaan</th>
        <th>Masuk</th>
        <th>Pulang</th>
        <th>Durasi</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr></thead>
    <tbody>
        @forelse($rows as $i => $r)
            @php
                $statusClass = 's-' . $r->status;
                $durasi = '';
                if ($r->jam_masuk && $r->jam_pulang) {
                    $d = \Carbon\Carbon::parse($r->jam_masuk)->diff(\Carbon\Carbon::parse($r->jam_pulang));
                    $durasi = "{$d->h}j {$d->i}m";
                }
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                <td>{{ $r->siswa->user->name ?? '-' }}<br><small>{{ $r->siswa->nis ?? '' }}</small></td>
                <td>{{ $r->penempatan->perusahaan->nama ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($r->jam_masuk)->format('H:i:s') }}</td>
                <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i:s') : '-' }}</td>
                <td>{{ $durasi ?: '-' }}</td>
                <td><span class="badge {{ $statusClass }}">{{ strtoupper($r->status) }}</span></td>
                <td>{{ $r->keterangan ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" style="text-align:center;">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} — Halaman 1</div>
</body></html>