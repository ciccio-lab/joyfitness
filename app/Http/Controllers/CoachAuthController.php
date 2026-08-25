<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachAuthController extends Controller
{
    public function showLoginForm()
    {
        // Se la tua vista si trova in resources/views/login.blade.php
        if (view()->exists('login')) {
            return view('login');
        }

        // Se la vista si trova in resources/views/auth/login.blade.php
        if (view()->exists('auth.login')) {
            return view('auth.login');
        }

        return view('coach.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('coach')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $coach = Auth::guard('coach')->user();

            return redirect()->intended(route('coach.dashboard', ['coach' => $coach->slug ?? $coach->id]));
        }

        return back()->withErrors([
            'email' => 'Le credenziali inserite non sono corrette.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('coach')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}