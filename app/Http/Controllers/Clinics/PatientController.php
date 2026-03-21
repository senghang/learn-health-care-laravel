<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PatientController extends Controller
{
    public function list(): View
    {
        return view('clinics.patients.index');
    }
}
