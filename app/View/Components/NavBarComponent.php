<?php

namespace App\View\Components;

use Auth;
use Illuminate\View\Component;

class NavBarComponent extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('v2.front.components.navbar', [
            'is_logged_in' => Auth::check(),
        ]);
    }
}
