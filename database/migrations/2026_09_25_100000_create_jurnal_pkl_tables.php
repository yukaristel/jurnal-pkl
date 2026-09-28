<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('npsn')->unique();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('perusahaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->string('telepon')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->nullable();
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->string('nis')->unique();
            $table->string('kelas')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('telepon')->nullable();
            $table->timestamps();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->string('nip')->unique();
            $table->string('telepon')->nullable();
            $table->timestamps();
        });

        Schema::create('pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->string('jabatan')->nullable();
            $table->string('telepon')->nullable();
            $table->timestamps();
        });

        Schema::create('penempatan_pkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbing')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['aktif', 'selesai', 'batal'])->default('aktif');
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index('pembimbing_id');
            $table->index('guru_id');
        });

        Schema::create('pengaturan_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->tinyInteger('hari')->comment('1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu');
            $table->time('jam_masuk');
            $table->time('batas_terlambat');
            $table->time('jam_pulang_min');
            $table->boolean('libur')->default(false);
            $table->timestamps();

            $table->unique(['perusahaan_id', 'hari']);
        });

        Schema::create('hari_libur', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->timestamps();
            $table->unique('tanggal');
        });

        Schema::create('qr_token', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembimbing_id')->constrained('pembimbing')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('expired_at')->index();
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('penempatan_id')->constrained('penempatan_pkl')->cascadeOnDelete();
            $table->foreignId('qr_token_id')->nullable()->constrained('qr_token')->nullOnDelete();
            $table->date('tanggal');
            $table->dateTime('jam_masuk');
            $table->dateTime('jam_pulang')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->string('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal']);
            $table->index('status');
        });

        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('penempatan_id')->constrained('penempatan_pkl')->cascadeOnDelete();
            $table->foreignId('absensi_id')->nullable()->constrained('absensi')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('jurnal')->nullOnDelete();
            $table->date('tanggal');
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('kesulitan')->nullable();
            $table->enum('status', ['on_progress', 'done', 'hold', 'reprogress'])->default('on_progress');
            $table->timestamp('hold_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal']);
            $table->index('status');
        });

        Schema::create('jurnal_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnal')->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->nullable();
            $table->timestamps();
        });

        Schema::create('jurnal_status_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnal')->cascadeOnDelete();
            $table->string('dari_status')->nullable();
            $table->string('ke_status');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('waktu');
            $table->timestamps();
        });

        Schema::create('catatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->nullable()->constrained('jurnal')->nullOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipe', ['catatan_jurnal', 'pesan']);
            $table->text('isi');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'dibaca_at']);
        });

        Schema::create('proyek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('penempatan_id')->constrained('penempatan_pkl')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->foreignId('diberikan_oleh')->constrained('users');
            $table->date('tanggal_mulai');
            $table->date('target_selesai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['belum_mulai', 'berjalan', 'selesai', 'batal'])->default('belum_mulai');
            $table->text('hasil')->nullable();
            $table->timestamps();
        });

        Schema::create('proyek_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyek')->cascadeOnDelete();
            $table->text('keterangan');
            $table->unsignedTinyInteger('persen');
            $table->string('lampiran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek_progress');
        Schema::dropIfExists('proyek');
        Schema::dropIfExists('catatan');
        Schema::dropIfExists('jurnal_status_log');
        Schema::dropIfExists('jurnal_lampiran');
        Schema::dropIfExists('jurnal');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('qr_token');
        Schema::dropIfExists('hari_libur');
        Schema::dropIfExists('pengaturan_absensi');
        Schema::dropIfExists('penempatan_pkl');
        Schema::dropIfExists('pembimbing');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('perusahaan');
        Schema::dropIfExists('sekolah');
    }
};