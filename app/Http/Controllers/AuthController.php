<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:merchant,customer'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $role = $data['role'];
        $user = User::create(array_diff_key($data, ['role' => true]));
        $user->role = $role;
        $user->save();

        if ($user->role === 'merchant') {
            Laundry::create([
                'user_id' => $user->id,
                'name' => 'Laundry '.Str::title(Str::before($user->name, ' ')),
                'slug' => Str::slug($user->name).'-'.Str::lower(Str::random(4)),
                'phone' => $user->phone,
                'address' => 'Lengkapi alamat outlet Anda.',
                'description' => 'Merchant laundry baru di SafeWash, siap menerima order digital dengan onboarding gratis.',
                'subscription_plan' => 'free-signup',
                'commission_rate' => 15,
                'service_fee_rate' => 3,
                'is_active' => true,
                'premium_protection_enabled' => true,
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
