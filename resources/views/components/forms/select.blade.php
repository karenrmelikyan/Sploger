<!-- /resources/views/components/forms/select.blade.php -->
@props(['selected', 'placeholder', 'options' => []])
@php
/**
 * @var \Illuminate\View\ComponentAttributeBag $attributes
 * @var \Illuminate\Support\ViewErrorBag $errors
 * @var \Illuminate\Support\HtmlString $slot
 * @var string $placeholder
 * @var array $options
 */
$name = $attributes->get('name');
$hasError = $errors->has($name);
if ($hasError) {
    $attributes = $attributes->merge([
        'aria-describedby' => 'validationFeedback' . ucfirst($name),
    ]);
}
$selected = $selected ?? '';
@endphp
<!--suppress HtmlFormInputWithoutLabel -->
<select {{ $attributes->class(['form-select', 'is-invalid' => $hasError]) }}>
    @if (isset($placeholder))
        <option {{ $selected == '' ? "selected" : '' }} disabled>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $key => $text)
        <option {{ $selected == $key ? "selected" : '' }} value="{{ $key }}">{{ $text }}</option>
    @endforeach
</select>
<div class="invalid-feedback" id="validationFeedback{{ ucfirst($name) }}">
    @if($hasError)
        {{ $errors->first($name) }}
    @endIf
</div>
