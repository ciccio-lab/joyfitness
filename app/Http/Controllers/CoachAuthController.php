<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachAuthController extends Controller
{
    public function showLoginForm()
    {
        if (view()->exists('coach_login')) {
            return view('coach_login');
        }

        return view('welcome');
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