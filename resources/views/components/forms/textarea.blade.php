@props(['value'])
@php
/**
 * @var \Illuminate\View\ComponentAttributeBag $attributes
 * @var \Illuminate\Support\ViewErrorBag $errors
 * @var \Illuminate\Support\HtmlString $slot
*/
$name = $attributes->get('name');
$hasError = $errors->has($name);
if ($hasError) {
    $attributes = $attributes->merge([
        'aria-describedby' => 'validationFeedback' . ucfirst($name),
    ]);
}
@endphp
<!--suppress HtmlFormInputWithoutLabel -->
<textarea {{ $attributes->class(['form-control', 'form-control-textarea', 'is-invalid' => $hasError]) }}>{{ $value ?? $slot }}</textarea>
<div class="invalid-feedback" id="validationFeedback{{ ucfirst($name) }}">
    @if($hasError)
        {{ $errors->first($name) }}
    @endIf
</div>
