@php
/**
 * @var array $keywordSets
 * @var array $servers
 */
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Create project') }}</x-slot>
    <x-forms.validated-form method="post" action="{{ route('projects.store') }}">
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="name">{{ __('Name') }}</x-forms.label>
            <div class="col-sm-9">
                <x-forms.input type="text" name="name" id="name" :value="old('name')" required autofocus />
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_set_id">{{ __('Keyword Set') }}</x-forms.label>
            <div class="col-sm-9">
                <x-forms.select id="keyword_set_id" name="keyword_set_id" placeholder="{{ __('Select keyword set') }}" :options="$keywordSets" :selected="old('keyword_set_id')" required />
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_set_id">{{ __('Server') }}</x-forms.label>
            <div class="col-sm-9">
                <x-forms.select id="server_id" name="server_id" placeholder="{{ __('Select server') }}" :options="$servers" :selected="old('server_id')" required />
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="sections_from">{{ __('Sections (from - to)') }}</x-forms.label>
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-6">
                        <x-forms.input type="number" name="sections_from" id="sections_from" :value="old('sections_from', 3)" min="1" step="1" required />
                    </div>
                    <div class="col-6">
                        <x-forms.input type="number" name="sections_to" id="sections_to" :value="old('sections_to', 6)" min="1" step="1" required />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="words_from">{{ __('Words per section (from - to)') }}</x-forms.label>
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-6">
                        <x-forms.input type="number" name="words_from" id="words_from" :value="old('words_from', 150)" min="1" step="1" required />
                    </div>
                    <div class="col-6">
                        <x-forms.input type="number" name="words_to" id="words_to" :value="old('words_to', 300)" min="1" step="1" required />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_density">{{ __('Keyword density') }}</x-forms.label>
            <div class="col-sm-9">
                <x-forms.input type="number" name="keyword_density" id="keyword_density" placeholder="{{ __('Leave empty, not to enforce keyword density') }}" :value="old('keyword_density')" min="0" step="1" max="100" append="%" />
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="sections_from">{{ __('Post scheduling') }}</x-forms.label>
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-2 col-form-label">1 post every</div>
                    <div class="col-3">
                        <x-forms.input type="number" name="schedule_interval" id="schedule_interval" :value="old('schedule_interval')" placeholder="{{ __('Leave empty, not to enforce.') }}" min="1" step="1" required />
                    </div>
                    <div class="col-3 col-form-label">minutes, with variance of</div>
                    <div class="col-3">
                        <x-forms.input type="number" name="schedule_variance" id="schedule_variance" :value="old('schedule_variance')" placeholder="{{ __('Leave empty, not to enforce.') }}" min="1" step="1" required />
                    </div>
                    <div class="col-1 col-form-label">minutes.</div>
                </div>
            </div>
        </div>
        <div class="row mt-5 mb-3">
            <div class="col">
                <table class="project-splogs table table-bordered table-sm caption-top">
                    <caption>{{ __('List of splogs') }} (Leave fields empty for project defaults)<br /> <small class="fst-italic">(Do not create more than 8 splogs in one go, as RunCloud has rate limits, add more splogs after creating project)</small> <button type="button" class="add-splog btn btn-primary btn-sm float-end">Add splog</button></caption>
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('Domain') }}</th>
                        <th scope="col">{{ __('Server') }}</th>
                        <th scope="col" style="width: 160px">{{ __('Sections (from - to)') }}</th>
                        <th scope="col" style="width: 230px">{{ __('Words per section (from - to)') }}</th>
                        <th scope="col" style="width: 70px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (old('splogs') === null)
                        <tr>
                            <th scope="row">1</th>
                            <td>
                                <x-forms.input type="text" class="form-control-sm" name="splogs[1][domain]" :value="old('splogs')[1]['domain'] ?? ''" required />
                            </td>
                            <td>
                                <x-forms.select name="splogs[1][server_id]" class="form-select-sm" placeholder="{{ __('Select server') }}" :options="$servers" :selected="old('splogs')[1]['server'] ?? ''" />
                            </td>
                            <td>
                                <div class="row g-1">
                                    <div class="col">
                                        <x-forms.input type="number" class="form-control-sm" name="splogs[1][sections_from]" min="1" step="1" :value="old('splogs')[1]['sections_from'] ?? ''" />
                                    </div>
                                    <div class="col">
                                        <x-forms.input type="number" class="form-control-sm" name="splogs[1][sections_to]" min="1" step="1" :value="old('splogs')[1]['sections_to'] ?? ''" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row g-1">
                                    <div class="col">
                                        <x-forms.input type="number" class="form-control-sm" name="splogs[1][words_from]" min="1" step="1" :value="old('splogs')[1]['words_from'] ?? ''" />
                                    </div>
                                    <div class="col">
                                        <x-forms.input type="number" class="form-control-sm" name="splogs[1][words_to]" min="1" step="1" :value="old('splogs')[1]['words_to'] ?? ''" />
                                    </div>
                                </div>
                            </td>
                            <td class="actions text-center">
                                <x-button type="button" class="btn-sm btn-danger remove-splog" aria-label="{{ __('Remove from project') }}">
                                    <span aria-hidden="true" class="bi-trash"></span>
                                </x-button>
                            </td>
                        </tr>
                    @else
                        @foreach (old('splogs') as $id => $splog)
                            <tr>
                                <th scope="row">{{ $id }}</th>
                                <td>
                                    <x-forms.input type="text" class="form-control-sm" name="splogs[{{ $id }}][domain]" :value="$splog['domain'] ?? ''" required />
                                </td>
                                <td>
                                    <x-forms.select name="splogs[{{ $id }}][server_id]" class="form-select-sm" placeholder="{{ __('Select server') }}" :options="$servers" :selected="$splog['server_id'] ?? ''" />
                                </td>
                                <td>
                                    <div class="row g-1">
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][sections_from]" min="1" step="1" :value="$splog['sections_from'] ?? ''" />
                                        </div>
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][sections_to]" min="1" step="1" :value="$splog['sections_to'] ?? ''" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row g-1">
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][words_from]" min="1" step="1" :value="$splog['words_from'] ?? ''" />
                                        </div>
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][words_to]" min="1" step="1" :value="$splog['words_to'] ?? ''" />
                                        </div>
                                    </div>
                                </td>
                                <td class="actions text-center">
                                    <x-button type="button" class="btn-sm btn-danger remove-splog" aria-label="{{ __('Remove from project') }}">
                                        <span aria-hidden="true" class="bi-trash"></span>
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
        <x-button class="btn-primary">{{ __('Save') }}</x-button>
    </x-forms.validated-form>
</x-app-layout>
<template id="splog-template">
    <tr>
        <th scope="row">:id</th>
        <td>
            <x-forms.input type="text" class="splog-domain form-control-sm" name="splogs[:id][domain]" required />
        </td>
        <td>
            <x-forms.select class="splog-server form-select-sm" name="splogs[:id][server_id]" placeholder="{{ __('Select server') }}" :options="$servers" />
        </td>
        <td>
            <div class="row g-1">
                <div class="col">
                    <x-forms.input type="number" class="splog-sections-from form-control-sm" name="splogs[:id][sections_from]" min="1" step="1" />
                </div>
                <div class="col">
                    <x-forms.input type="number" class="splog-sections-to form-control-sm" name="splogs[:id][sections_to]" min="1" step="1" />
                </div>
            </div>
        </td>
        <td>
            <div class="row g-1">
                <div class="col">
                    <x-forms.input type="number" class="splog-words-from form-control-sm" name="splogs[:id][words_from]" min="1" step="1" />
                </div>
                <div class="col">
                    <x-forms.input type="number" class="splog-words-from form-control-sm" name="splogs[:id][words_to]" min="1" step="1" />
                </div>
            </div>
        </td>
        <td class="actions text-center">
            <x-button type="button" class="btn-sm btn-danger remove-splog" aria-label="{{ __('Remove from project') }}">
                <span aria-hidden="true" class="bi-trash"></span>
            </x-button>
        </td>
    </tr>
</template>
