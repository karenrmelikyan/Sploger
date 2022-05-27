<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function view;

final class AppLayout extends Component
{
    public function render(): View
    {
        return view('layouts.app');
    }
}
