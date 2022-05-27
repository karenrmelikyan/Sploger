@props([
    'label' => null,
    'options' => []
])
@php
/**
* @var \Illuminate\View\ComponentAttributeBag $attributes
* @var \Illuminate\Support\HtmlString $slot
* @var string|null $label
* @var array $options
*/
$name = $attributes->get('name');
$selected = request()->input('filter')[$name] ?? '';
if ($name !== null) {
    $attributes['name'] = "filter[$name]";
}
@endphp
<div class="filter">
    <select {{ $attributes->class(['form-select form-select-sm']) }} aria-label="{{ $label ?? '' }}">
        <option {{ $selected === '' ? "selected" : '' }} value="">{{ $label }}</option>
        @foreach ($options as $key => $text)
            <option @if ($selected === (string) $key) selected @endif value="{{ $key }}">{{ $text }}</option>
        @endforeach
    </select>
</div>
