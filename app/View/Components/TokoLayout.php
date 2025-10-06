<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class TokoLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $role = Auth::user()->role ?? 'guest';
        if ($role == 'Teknisi') {
            return view('layouts.teknisi');
        }elseif ($role == 'Sales') {
            return view('layouts.sales');
        }elseif ($role == 'Admin Toko') {
            return view('layouts.admin');
        }
        
        return view('layouts.toko');
    }
}
