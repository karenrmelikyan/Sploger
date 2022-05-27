@props(['active', 'icon'])

@php
$classes = ($active ?? false)
    ? 'nav-link active'
    : 'nav-link'
@endphp
<a {{ $attributes->merge(['class' => $classes]) }} {{ $active ? 'aria-current="page"' : '' }}>
    {!! isset($icon) ? '<span class="' . $icon . '"></span>' : '' !!}
    {{ $slot }}
</a>
