<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'city'     => ['nullable', 'string', 'max:100'],
            'address'  => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $update = [
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'city'    => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
