<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        if ($user && isset($user->email)) {
            $email = strtolower($user->email);

            // Admin → Admin dashboard
            if ($email === 'admin@test.com') {
                return redirect()->intended(route('admin.bookings', absolute: false));
            }
            
            // Event owner → Events page
            if ($email === 'eventowner@test.com') {
                return redirect()->intended(route('events.index', absolute: false));
            }

            // Ferry owner:
            if ($email === 'ferryowner@test.com') {
                return redirect()->intended(route('ferry.schedules', absolute: false));
            }

            if ($email === 'hotelowner@test.com') {
                return redirect()->intended(route('rooms.index', absolute: false));
            }
        }

        // Everyone else → Dashboard (or their originally intended URL)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}