<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    /**
     * Tampilkan daftar seluruh karyawan.
     */
    public function index()
    {
        $karyawans = Karyawan::orderBy('nama')->get();

        return view('admin.master-karyawan', compact('karyawans'));
    }

    /**
     * Tampilkan form tambah karyawan.
     */
    public function create()
    {
        return view('admin.karyawan-form', [
            'karyawan' => null,
        ]);
    }

    /**
     * Simpan karyawan baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nip'     => 'required|string|max:20|unique:karyawans,nip',
            'nama'    => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
        ]);

        Karyawan::create($data);

        return redirect()->route('admin.master.karyawan')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit karyawan.
     */
    public function edit(Karyawan $karyawan)
    {
        return view('admin.karyawan-form', compact('karyawan'));
    }

    /**
     * Perbarui data karyawan.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $request->validate([
            'nip'     => ['required', 'string', 'max:20', Rule::unique('karyawans', 'nip')->ignore($karyawan->id)],
            'nama'    => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
        ]);

        $karyawan->update($data);

        return redirect()->route('admin.master.karyawan')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Hapus data karyawan.
     */
    public function destroy(Karyawan $karyawan)
    {
        // Cegah hapus jika sudah punya riwayat absensi, agar data histori tidak rusak
        if ($karyawan->absensis()->exists()) {
            return back()->with('error', 'Karyawan tidak dapat dihapus karena sudah memiliki riwayat absensi. Riwayat wajib diarsipkan terlebih dahulu.');
        }

        $karyawan->delete();

        return redirect()->route('admin.master.karyawan')->with('success', 'Data karyawan berhasil dihapus.');
    }
}