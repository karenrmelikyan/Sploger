@php
/**
 * @var \App\Models\Setting $setting
 * @var string $value
*/
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag, HtmlFormInputWithoutLabel -->
<x-app-layout>
    <x-slot name="header">{{ __('Edit settings') }}</x-slot>
    <div class="row">
        <div class="col-sm-6">
            <x-forms.validated-form method="post" action="{{ route('settings.store') }}">
                <x-forms.input type="hidden" name="id" :value="$setting->id"/>
                <div class="row mb-3">
                    <x-forms.label class="col-sm-3" :for-column="true" for="name">{{ __('Name') }}</x-forms.label>
                    <div class="col-sm-9">
                        <input type="text" id="name" name="name" readonly class="form-control-plaintext" value="{{ $setting->name }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <x-forms.label class="col-sm-3" :for-column="true" for="email">{{ __('Value') }}</x-forms.label>
                    <div class="col-sm-9">
                        <x-forms.textarea name="value" id="value" rows="10" aria-describedby="valueHelp" required>
                            {{ $value }}
                        </x-forms.textarea>
                        <div id="valueHelp" class="form-text">{{ __('You must input one value per line.') }}</div>
                    </div>
                </div>
                <x-button class="btn-primary">{{ __('Save') }}</x-button>
            </x-forms.validated-form>
        </div>
    </div>
</x-app-layout>
