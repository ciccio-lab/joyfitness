<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CoachAuthController extends Controller
{
    public function showLoginForm()
    {
        $coaches = Coach::all();
        return view('coach_login', compact('coaches'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'slug' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $coach = Coach::where('slug', $request->slug)->first();

        if ($coach && Hash::check($request->password, $coach->password)) {
            Auth::guard('coach')->login($coach, $request->has('remember'));
            $request->session()->regenerate();

            return redirect()->route('coach.dashboard', ['coach' => $coach->slug]);
        }

        return back()->withErrors([
            'password' => 'Credenziali non valide o password errata.',
        ])->onlyInput('slug');
    }

    public function logout(Request $request)
    {
        Auth::guard('coach')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}