<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        // show your home/dashboard view
        return view('dashboard');   // or: return view('welcome');
    }
}
