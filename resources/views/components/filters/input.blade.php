@props([
    'label' => null,
])
@php
/**
* @var \Illuminate\View\ComponentAttributeBag $attributes
* @var \Illuminate\Support\HtmlString $slot
* @var string|null $label
* @var array $options
*/
$name = $attributes->get('name');
$value = request()->input('filter')[$name] ?? '';
if ($name !== null) {
    $attributes['name'] = "filter[$name]";
}
@endphp
<div class="filter">
    <input type="text" {{ $attributes->class(['form-control form-control-sm filters-input']) }} aria-label="{{ $label ?? '' }}" placeholder="{{ $label ?? '' }}" value="{{ $value }}">
</div>
