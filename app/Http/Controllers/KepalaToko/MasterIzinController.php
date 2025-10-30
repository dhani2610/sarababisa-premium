<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;

class MasterIzinController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.izin'); // buat blade ini (di bawah)
    }
}
