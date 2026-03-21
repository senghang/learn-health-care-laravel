<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\ClinicModel;

class DashboardAdminController extends Controller
{
    
    public function index(){
        $data['ClinicCount'] = ClinicModel::count();
        // if (!Auth::guard('superadmin')->check()) {
        //         return redirect()->route('admin.login');
        //     }

        // if(Auth::check()){
        //     return redirect()->route('admin.login');
        // }
        return view('admin.dashobard',$data);
    }
}
