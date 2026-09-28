<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Proyek</title>
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
.s-bm { background: #64748b; } .s-bj { background: #2563eb; } .s-sl { background: #16a34a; } .s-bt { background: #dc2626; }
.footer { margin-top: 18px; font-size: 9px; color: #666; text-align: center; }
</style></head>
<body>
<h1>Laporan Proyek PKL</h1>
<div class="meta">Dicetak: {{ now()->format('d M Y H:i') }} WIB</div>
<div class="summary">
    <span><strong>Total:</strong> {{ $summary['total'] }}</span>
    <span><strong>Belum Mulai:</strong> {{ $summary['belum_mulai'] }}</span>
    <span><strong>Berjalan:</strong> {{ $summary['berjalan'] }}</span>
    <span><strong>Selesai:</strong> {{ $summary['selesai'] }}</span>
    <span><strong>Batal:</strong> {{ $summary['batal'] }}</span>
</div>
<table>
    <thead><tr>
        <th style="width:18px;">#</th>
        <th>Siswa</th>
        <th>Judul</th>
        <th>Periode</th>
        <th>Progress</th>
        <th>Status</th>
        <th>Hasil</th>
    </tr></thead>
    <tbody>
        @forelse($rows as $i => $p)
            @php
                $last = $p->progress->last();
                $persen = $last?->persen ?? 0;
                $sc = match($p->status) { 'belum_mulai' => 's-bm', 'berjalan' => 's-bj', 'selesai' => 's-sl', 'batal' => 's-bt', default => 's-bm' };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->siswa->user->name ?? '-' }}<br><small>{{ $p->siswa->nis ?? '' }}</small></td>
                <td>{{ $p->judul }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($p->target_selesai)->format('d M Y') }}</td>
                <td><strong>{{ $persen }}%</strong> ({{ $p->progress->count() }} update)</td>
                <td><span class="badge {{ $sc }}">{{ strtoupper(str_replace('_',' ',$p->status)) }}</span></td>
                <td>{{ $p->hasil ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="footer">{{ config('app.name') }}</div>
</body></html>