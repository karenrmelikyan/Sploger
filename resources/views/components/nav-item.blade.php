@props(['route', 'icon'])

@php
$attrs = ($icon ?? false)
    ? ['href' => route($route), 'active' => request()->routeIs($route), 'icon' => $icon]
    : ['href' => route($route), 'active' => request()->routeIs($route)];
@endphp

<li class="nav-item">
    <x-nav-link {{ $attributes->merge($attrs) }}>
        {{ $slot }}
    </x-nav-link>
</li>
