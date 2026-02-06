<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class KasirController extends Controller
{
    /**
     * Display a listing of kasir users.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'kasir');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $kasirs = $query->with('layanan')->latest()->paginate(10);

        return view('layouts.kasir-users.index', compact('kasirs'));
    }

    /**
     * Show the form for creating a new kasir user.
     */
    public function create()
    {
        // Get current admin's layanan
        $adminLayanan = auth()->user()->layanan;

        // If admin has no layanan, show all layanans
        if (!$adminLayanan) {
            $layanans = Layanan::all();
        } else {
            // Admin can only assign layanan that they own
            $layanans = Layanan::where('id', $adminLayanan->id)->get();
        }

        return view('layouts.kasir-users.create', compact('layanans'));
    }

    /**
     * Store a newly created kasir user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'layanan_id' => ['nullable', 'exists:layanan,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'layanan_id' => $request->layanan_id,
            'role' => 'kasir',
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('kasir-users.index')
            ->with('success', 'Kasir berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified kasir user.
     */
    public function edit(User $kasirUser)
    {
        // Ensure we're only editing kasir users
        if ($kasirUser->role !== 'kasir') {
            abort(403, 'Unauthorized action.');
        }

        // Get current admin's layanan
        $adminLayanan = auth()->user()->layanan;

        // If admin has no layanan, show all layanans
        if (!$adminLayanan) {
            $layanans = Layanan::all();
        } else {
            // Admin can only assign layanan that they own
            $layanans = Layanan::where('id', $adminLayanan->id)->get();
        }

        return view('layouts.kasir-users.edit', compact('kasirUser', 'layanans'));
    }

    /**
     * Update the specified kasir user in storage.
     */
    public function update(Request $request, User $kasirUser)
    {
        // Ensure we're only updating kasir users
        if ($kasirUser->role !== 'kasir') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $kasirUser->id],
            'layanan_id' => ['nullable', 'exists:layanan,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $kasirUser->name = $request->name;
        $kasirUser->email = $request->email;
        $kasirUser->layanan_id = $request->layanan_id;

        // Only update password if provided
        if ($request->filled('password')) {
            $kasirUser->password = Hash::make($request->password);
        }

        $kasirUser->save();

        return redirect()->route('kasir-users.index')
            ->with('success', 'Kasir berhasil diperbarui.');
    }

    /**
     * Remove the specified kasir user from storage.
     */
    public function destroy(User $kasirUser)
    {
        // Ensure we're only deleting kasir users
        if ($kasirUser->role !== 'kasir') {
            abort(403, 'Unauthorized action.');
        }

        $kasirUser->delete();

        return redirect()->route('kasir-users.index')
            ->with('success', 'Kasir berhasil dihapus.');
    }
}
