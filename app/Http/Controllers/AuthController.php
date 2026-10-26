<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $r)
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);

        if (! Auth::attempt($cred, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $r->session()->regenerate();

        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('shop.index'));
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'account_type' => 'required|in:B2C,B2B',
            'company_name' => 'nullable|string|max:120|required_if:account_type,B2B',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create($data + ['role' => 'customer']);
        Auth::login($user);
        $r->session()->regenerate();

        return redirect()->route('shop.index')->with('success', 'Akun berhasil dibuat. Selamat berbelanja!');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('shop.index');
    }
}
