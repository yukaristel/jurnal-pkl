<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseAbsentWithoutPulang extends Command
{
    protected $signature = 'absensi:close-pending';

    protected $description = 'Tutup absensi yang lupa pulang (jam_pulang masih null) kemarin & sebelumnya';

    public function handle(): int
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        $rows = Absensi::whereNull('jam_pulang')
            ->whereDate('tanggal', '<=', $yesterday)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->get();

        if ($rows->isEmpty()) {
            $this->info('Tidak ada absensi pending yang perlu ditutup.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($rows as $row) {
            $tanggal = Carbon::parse($row->tanggal);
            $jamPulangDefault = $tanggal->isWeekend() ? '14:00:00' : '16:00:00';
            $row->update([
                'jam_pulang' => $tanggal->copy()->setTimeFromTimeString($jamPulangDefault),
                'keterangan' => ($row->keterangan ? $row->keterangan . ' | ' : '') . '[Auto-close: lupa absen pulang]',
            ]);
            $count++;
        }

        $this->info("Berhasil menutup {$count} absensi yang lupa pulang (sampai tanggal {$yesterday}).");
        return self::SUCCESS;
    }
}