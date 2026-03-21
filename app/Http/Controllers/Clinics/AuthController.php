<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('clinics.auth.login');
    }

    public function authLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials['clinic_id']  = currentClinic()->id;
        $credentials['is_deleted'] = 0;

        $remember = !empty($request->remember);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'Email or password is incorrect.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // FIXED: was redirecting with a 'subdomain' parameter that route('login')
        // doesn't accept — just redirect to the named login route directly.
        return redirect()->route('login');
    }
}
