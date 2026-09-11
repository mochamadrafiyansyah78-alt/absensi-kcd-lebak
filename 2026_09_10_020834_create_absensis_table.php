<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit']);
            $table->enum('tipe_absensi', ['masuk', 'pulang'])->nullable();
            $table->time('jam')->nullable();
            $table->enum('status_waktu', ['tepat_waktu', 'terlambat'])->nullable();
            $table->string('foto_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            // Mencegah duplikasi absen masuk/pulang di tanggal yang sama
            $table->unique(['karyawan_id', 'tanggal', 'tipe_absensi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};