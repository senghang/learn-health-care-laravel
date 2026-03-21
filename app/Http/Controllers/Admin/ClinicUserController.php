<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClinicUserController extends Controller
{
    public function index(){
        return view('admin.clinic.user.clinic_user_list');
    }
}
