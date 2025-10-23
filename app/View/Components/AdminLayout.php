<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        if (Auth::check() == null) {
            return redirect('/login');
        }
        $role = Auth::user()->role ?? 'guest';
        if ($role == 'Teknisi') {
            return view('layouts.teknisi');
        }

        return view('layouts.admin');
    }
}
