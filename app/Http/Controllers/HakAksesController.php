<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class HakAksesController extends Controller
{
    public function index()
    {
        if (auth()->check() == null) {
            return redirect('/login')->with('error', 'silahkan login kembali,session anda telah habis.');
        }
        return view('pages/hak-akses');
    }
}
