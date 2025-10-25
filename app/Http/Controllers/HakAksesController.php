<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class HakAksesController extends Controller
{
    public function index()
    {
        if (auth()->check() == null) {
            return redirect('/login')->with('error', 'silahkan login kembali,session anda telah habis.');
        }
        $setting = User::find(1);
        return view('pages/hak-akses',compact('setting'));
    }
}
