<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Layanan::with('lokasis');

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_kasir', 'like', '%' . $request->search . '%');
            });
        }

        $layanans = $query->withCount('users', 'lokasis')->paginate(10);

        // dd($layanans);

        return view('layouts.layanan.index', compact('layanans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.layanan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_kasir' => ['required', 'string', 'max:255', 'unique:layanan,kode_kasir'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        Layanan::create([
            'kode_kasir' => $request->kode_kasir,
            'nama' => $request->nama,
        ]);

        return redirect()->route('layanan.index')
            ->with('message', 'Layanan berhasil ditambahkan!');
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
        $layanan = Layanan::with('lokasis')->findOrFail($id);
        return view('layouts.layanan.edit', compact('layanan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $layanan = Layanan::findOrFail($id);

        $request->validate([
            'kode_kasir' => ['required', 'string', 'max:255', 'unique:layanan,kode_kasir,' . $id],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $layanan->update([
            'kode_kasir' => $request->kode_kasir,
            'nama' => $request->nama,
        ]);

        return redirect()->route('layanan.index')
            ->with('message', 'Layanan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $layanan = Layanan::findOrFail($id);

        // Check if layanan has related users
        if ($layanan->users()->count() > 0) {
            return redirect()->route('layanan.index')
                ->with('error', 'Layanan tidak dapat dihapus karena masih memiliki user terkait!');
        }

        $layanan->delete();

        return redirect()->route('layanan.index')
            ->with('message', 'Layanan berhasil dihapus!');
    }
}
