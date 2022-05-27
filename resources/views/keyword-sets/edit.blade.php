@php
/**
 * @var \App\Models\KeywordSet $set
 * @var string $keywords
*/
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Edit keyword set') }}</x-slot>
    <div class="row">
        <div class="col-sm-6">
            <x-forms.validated-form method="post" action="{{ route('keyword-sets.store') }}">
                <x-forms.input type="hidden" name="id" :value="$set->id"/>
                <div class="row mb-3">
                    <x-forms.label class="col-sm-3" :for-column="true" for="name">{{ __('Name') }}</x-forms.label>
                    <div class="col-sm-9">
                        <x-forms.input type="text" name="name" id="name" :value="$set->name" required autofocus />
                    </div>
                </div>
                <div class="row mb-3">
                    <x-forms.label class="col-sm-3" :for-column="true" for="language_code">{{ __('Language') }}</x-forms.label>
                    <div class="col-sm-9">
                        <x-forms.select id="language_code" name="language_code" placeholder="{{ __('Select language') }}" :options="$languages" :selected="$language_code" required />
                    </div>
                </div>
                <div class="row mb-3">
                    <x-forms.label class="col-sm-3" :for-column="true" for="email">{{ __('Keywords') }}</x-forms.label>
                    <div class="col-sm-9">
                        <x-forms.textarea name="keywords" id="keywords" rows="10" aria-describedby="keywordsHelp" required>
                            {{ $keywords }}
                        </x-forms.textarea>
                        <div id="keywordsHelp" class="form-text">{{ __('You must input one keywords per line.') }}</div>
                    </div>
                </div>
                <x-button class="btn-primary">{{ __('Save') }}</x-button>
            </x-forms.validated-form>
        </div>
    </div>
</x-app-layout>
