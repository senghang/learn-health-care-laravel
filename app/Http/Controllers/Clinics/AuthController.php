<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (!empty(Auth::check())) {
            return redirect()->route('dashboard');
        }

        // dd(currentClinic()->id); // call clinic id from subdomain
        return view('clinics.auth.login');
    }

    public function authLogin(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required',]);
        // attach clinic_id
        $credentials['clinic_id'] = currentClinic()->id;
        $credentials['is_deleted'] = 0;
        $remember = !empty($request->remember);
        // if(Auth::attempt(['email' => $request->email, 'password' => $request->password , 'is_deleted' => 0 ], $remember))
        if (Auth::attempt($credentials, $remember)) {
            // $request->session()->regenerate();
            return redirect('backend/dashboard');
        } else {
            return redirect()->back()->with('error', 'Please enter current email and password');
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // clinic user
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login', ['subdomain' => currentClinic()->subdomain]);
    }
}
