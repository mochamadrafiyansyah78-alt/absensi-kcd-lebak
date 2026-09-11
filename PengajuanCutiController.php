<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use Illuminate\Http\Request;

class PengajuanCutiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $cutis = Cuti::with('karyawan')
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.pengajuan-cuti', compact('cutis', 'status'));
    }

    public function setujui(Cuti $cuti)
    {
        $cuti->update(['status' => 'disetujui', 'catatan_admin' => null]);

        return back()->with('success', 'Pengajuan cuti ' . $cuti->karyawan->nama . ' telah disetujui.');
    }

    public function tolak(Request $request, Cuti $cuti)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $cuti->update([
            'status'        => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Pengajuan cuti ' . $cuti->karyawan->nama . ' telah ditolak.');
    }
}