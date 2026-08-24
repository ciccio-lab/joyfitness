<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Coach;

class CoachAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $coachParam = $request->route('coach');

        if (!$coachParam instanceof Coach) {
            $coachParam = Coach::where('slug', $coachParam)->first();
        }

        if (!session()->has('coach_logged_in') || session('coach_logged_in') != optional($coachParam)->id) {
            return redirect()->route('coach.login')->with('error', 'Effettua prima il login per accedere alla dashboard!');
        }

        return $next($request);
    }
}