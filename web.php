<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PengajuanCutiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/absen');
});

Route::get('/absen', [AbsensiController::class, 'index'])->name('absen.index');
Route::post('/absen', [AbsensiController::class, 'store'])->name('absen.store');

Route::get('/pengajuan-cuti', [CutiController::class, 'create'])->name('cuti.create');
Route::post('/pengajuan-cuti', [CutiController::class, 'store'])->name('cuti.store');

Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/rekap-harian', [AdminController::class, 'rekapHarian'])->name('rekap.harian');
    Route::get('/rekap-bulanan', [AdminController::class, 'rekapBulanan'])->name('rekap.bulanan');
    Route::get('/export/harian', [AdminController::class, 'exportHarian'])->name('export.harian');
    Route::get('/export/bulanan', [AdminController::class, 'exportBulanan'])->name('export.bulanan');

    Route::get('/master-karyawan', [KaryawanController::class, 'index'])->name('master.karyawan');
    Route::get('/master-karyawan/tambah', [KaryawanController::class, 'create'])->name('master.karyawan.create');
    Route::post('/master-karyawan', [KaryawanController::class, 'store'])->name('master.karyawan.store');
    Route::get('/master-karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('master.karyawan.edit');
    Route::put('/master-karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('master.karyawan.update');
    Route::delete('/master-karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('master.karyawan.destroy');

    Route::get('/hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
    Route::post('/hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
    Route::delete('/hari-libur/{hariLibur}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');

    Route::get('/pengajuan-cuti', [PengajuanCutiController::class, 'index'])->name('pengajuan-cuti.index');
    Route::post('/pengajuan-cuti/{cuti}/setujui', [PengajuanCutiController::class, 'setujui'])->name('pengajuan-cuti.setujui');
    Route::post('/pengajuan-cuti/{cuti}/tolak', [PengajuanCutiController::class, 'tolak'])->name('pengajuan-cuti.tolak');
});