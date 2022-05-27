@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {{$attributes->merge(['class' => 'form-control']) }} >
{{--@error($attributes->get('name'))--}}
<div class="invalid-feedback">
    Looks good!
</div>
{{--@enderror--}}
