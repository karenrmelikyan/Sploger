<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;

use function view;

final class GuestLayout extends Component
{
    public function render()
    {
        return view('layouts.guest');
    }
}
