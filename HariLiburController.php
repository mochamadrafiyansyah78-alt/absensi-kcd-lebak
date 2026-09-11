<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLiburs = HariLibur::orderBy('tanggal')->get();

        return view('admin.hari-libur', compact('hariLiburs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'    => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:150',
        ]);

        HariLibur::create($data);

        return back()->with('success', 'Hari libur nasional berhasil ditambahkan.');
    }

    public function destroy(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return back()->with('success', 'Hari libur nasional berhasil dihapus.');
    }
}