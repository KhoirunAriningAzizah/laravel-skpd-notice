<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lokasi::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $lokasis = $query->withCount('layanan')->paginate(10);

        return view('layouts.lokasi.index', compact('lokasis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $layanans = Layanan::all();
        return view('layouts.lokasi.create', compact('layanans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'layanan_id' => ['required', 'exists:layanan,id'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        Lokasi::create([
            'layanan_id' => $request->layanan_id,
            'nama' => $request->nama,
        ]);

        return redirect()->route('lokasi.index')
            ->with('message', 'Lokasi berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lokasi = Lokasi::with('layanan')->findOrFail($id);
        $layanans = Layanan::all();
        return view('layouts.lokasi.edit', compact('lokasi', 'layanans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lokasi = Lokasi::findOrFail($id);

        $request->validate([
            'layanan_id' => ['required', 'exists:layanan,id'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $lokasi->update([
            'layanan_id' => $request->layanan_id,
            'nama' => $request->nama,
        ]);

        return redirect()->route('lokasi.index')
            ->with('message', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lokasi = Lokasi::findOrFail($id);

        $lokasi->delete();

        return redirect()->route('lokasi.index')
            ->with('message', 'Lokasi berhasil dihapus!');
    }
}
