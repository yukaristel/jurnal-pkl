<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Jurnal</title>
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
.s-on { background: #2563eb; } .s-done { background: #16a34a; } .s-hold { background: #d97706; } .s-rep { background: #64748b; }
.footer { margin-top: 18px; font-size: 9px; color: #666; text-align: center; }
</style></head>
<body>
<h1>Laporan Jurnal Kegiatan PKL</h1>
<div class="meta">
    Dicetak: {{ now()->format('d M Y H:i') }} WIB
    @if(!empty($filters['dari']) && !empty($filters['sampai']))
        | Periode: {{ \Carbon\Carbon::parse($filters['dari'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['sampai'])->format('d M Y') }}
    @endif
</div>
<div class="summary">
    <span><strong>Total:</strong> {{ $summary['total'] }}</span>
    <span><strong>On Progress:</strong> {{ $summary['on_progress'] }}</span>
    <span><strong>Done:</strong> {{ $summary['done'] }}</span>
    <span><strong>Hold:</strong> {{ $summary['hold'] }}</span>
    <span><strong>Reprogress:</strong> {{ $summary['reprogress'] }}</span>
</div>
<table>
    <thead><tr>
        <th style="width:18px;">#</th>
        <th>Tanggal</th>
        <th>Siswa</th>
        <th>Judul</th>
        <th>Deskripsi</th>
        <th>Kesulitan</th>
        <th>Status</th>
        <th>Catatan</th>
    </tr></thead>
    <tbody>
        @forelse($rows as $i => $r)
            @php
                $sc = match($r->status) {
                    'on_progress' => 's-on',
                    'done' => 's-done',
                    'hold' => 's-hold',
                    'reprogress' => 's-rep',
                    default => 's-rep'
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                <td>{{ $r->siswa->user->name ?? '-' }}<br><small>{{ $r->siswa->nis ?? '' }}</small></td>
                <td>{{ $r->judul }}</td>
                <td>{{ \Illuminate\Support\Str::limit($r->deskripsi, 150) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($r->kesulitan ?? '-', 80) }}</td>
                <td><span class="badge {{ $sc }}">{{ strtoupper(str_replace('_',' ',$r->status)) }}</span></td>
                <td>{{ $r->catatan->count() }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="footer">{{ config('app.name') }}</div>
</body></html>