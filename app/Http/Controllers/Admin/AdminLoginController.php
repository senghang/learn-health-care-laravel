<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;



class AdminLoginController extends Controller
{
    //
    public function showLoginForm(){

        if (Auth::guard('superadmin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function admin_login(Request $request){
        // dd($request);
        if (Auth::guard('superadmin')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ],$request->remember)) {

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard'); 
        }

        return redirect()->back()->with('error', 'Please enter current email and password');
    }

    public function admin_logout(Request $request)
    {
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
