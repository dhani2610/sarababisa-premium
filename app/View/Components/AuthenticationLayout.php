<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;

class AuthenticationLayout extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $setting = User::find(1);

        return view('layouts.authentication', compact('setting'));
    }
}
