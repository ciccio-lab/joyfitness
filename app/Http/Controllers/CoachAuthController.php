<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
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
            'slug'     => 'required|string',
            'password' => 'required|string',
        ]);

        $coach = Coach::where('slug', $request->slug)->first();

        if ($coach && Hash::check($request->password, $coach->password)) {
            session(['coach_logged_in' => $coach->id]);
            return redirect()->route('coach.dashboard', $coach->slug);
        }

        return back()->with('error', 'Password errata o coach non trovato!');
    }

    public function logout()
    {
        session()->forget('coach_logged_in');
        return redirect()->route('coach.login')->with('success', 'Logout effettuato con successo!');
    }
}