@php
/**
 * @var array $keywordSets
 * @var array $servers
 * @var \App\Models\Project $project
 */
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag, HtmlFormInputWithoutLabel -->
<x-app-layout>
    <x-slot name="header">{{ __('Edit project') }}</x-slot>
    <x-forms.validated-form method="post" action="{{ route('projects.store') }}">
        <input type="hidden" name="id" value="{{ $project->id }}"/>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="name">{{ __('Name') }}</x-forms.label>
            <div class="col-sm-9">
                <x-forms.input type="text" name="name" id="name" :value="$project->name" required autofocus />
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_set_id">{{ __('Keyword Set') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="keyword_set_id" readonly class="form-control-plaintext" value="{{ $keywordSets[$project->keyword_set_id] }}">
                <input type="hidden" name="keyword_set_id" value="{{ $project->keyword_set_id }}" >
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_set_id">{{ __('Server') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="server_id" readonly class="form-control-plaintext" value="{{ $servers[$project->server_id] }}">
                <input type="hidden" name="server_id" value="{{ $project->server_id }}" >
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="sections_from">{{ __('Sections (from - to)') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="sections_from" readonly class="form-control-plaintext" value="{{ $project->sections_from . '-' . $project->sections_to }}">
                <input type="hidden" name="sections_from" value="{{ $project->sections_from }}" >
                <input type="hidden" name="sections_to" value="{{ $project->sections_to }}" >
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="words_from">{{ __('Words per section (from - to)') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="words_from" readonly class="form-control-plaintext" value="{{ $project->words_from . '-' . $project->words_to }}">
                <input type="hidden" name="words_from" value="{{ $project->words_from }}" >
                <input type="hidden" name="words_to" value="{{ $project->words_to }}" >
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="keyword_density">{{ __('Keyword density') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="keyword_density" name="keyword_density" readonly class="form-control-plaintext" value="{{ $project->keyword_density }}" placeholder="Not enforced">
            </div>
        </div>
        <div class="row mb-3">
            <x-forms.label class="col-sm-3" :for-column="true" for="words_from">{{ __('Post scheduling') }}</x-forms.label>
            <div class="col-sm-9">
                <input type="text" id="schedule" readonly class="form-control-plaintext" value="{{ $project->schedule_interval === null ? 'Not enforced' : '1 post every ' . $project->schedule_interval . ' minutes' . ($project->schedule_variance !== null ? ' with variance of ' . $project->schedule_variance . 'minutes' : 'without variance.') }}">
                <input type="hidden" name="schedule_interval" value="{{ $project->schedule_interval }}" >
                <input type="hidden" name="schedule_variance" value="{{ $project->schedule_variance }}" >
            </div>
        </div>
        <div class="row mt-5 mb-3">
            <div class="col">
                <table class="project-splogs table table-bordered table-sm caption-top">
                    <caption>{{ __('List of splogs') }} (Leave fields empty for project defaults)<br /> <small class="fst-italic">(Do not add more than 8 splogs in one go, as RunCloud has rate limits)</small> <button type="button" class="add-splog btn btn-primary btn-sm float-end">Add splog</button></caption>
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
                    @foreach ($project->splogs as $id => $currentSplog)
                        <tr>
                            <th scope="row">{{ $id + 1 }}</th>
                            <td>
                                <input type="text" class="form-control-sm form-control-plaintext" value="{{ $currentSplog->domain }}" readonly required />
                            </td>
                            <td>
                                <input type="text" class="form-control-sm form-control-plaintext" value="{{ $servers[$currentSplog->server_id] ?? 'project default' }}" readonly required />
                            </td>
                            <td>
                                <input type="text" class="form-control-sm form-control-plaintext" value="{{ $currentSplog->sections_from ? ($currentSplog->sections_from . '-' . $currentSplog->sections_to) : 'project default' }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control-sm form-control-plaintext" value="{{ $currentSplog->words_from ? ($currentSplog->words_from . '-' . $currentSplog->words_to) : 'project default' }}" readonly>
                            </td>
                            <td class="actions text-center">
                                <x-button type="button" class="btn-sm btn-danger remove-splog" data-splog-id="{{ $currentSplog->id }}" data-project-id="{{ $project->id }}" aria-label="{{ __('Delete splog') }}">
                                    <span aria-hidden="true" class="bi-trash"></span>
                                </x-button>
                            </td>
                        </tr>
                    @endforeach
                    @if (old('splogs') !== null)
                        @foreach (old('splogs') as $id => $oldSplog)
                            <tr>
                                <th scope="row">{{ $id }}</th>
                                <td>
                                    <x-forms.input type="text" class="form-control-sm" name="splogs[{{ $id }}][domain]" :value="$oldSplog['domain'] ?? ''" required />
                                </td>
                                <td>
                                    <x-forms.select name="splogs[{{ $id }}][server_id]" class="form-select-sm" placeholder="{{ __('Select server') }}" :options="$servers" :selected="$oldSplog['server_id'] ?? ''" />
                                </td>
                                <td>
                                    <div class="row g-1">
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][sections_from]" min="1" step="1" :value="$oldSplog['sections_from'] ?? ''" />
                                        </div>
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][sections_to]" min="1" step="1" :value="$oldSplog['sections_to'] ?? ''" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row g-1">
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][words_from]" min="1" step="1" :value="$oldSplog['words_from'] ?? ''" />
                                        </div>
                                        <div class="col">
                                            <x-forms.input type="number" class="form-control-sm" name="splogs[{{ $id }}][words_to]" min="1" step="1" :value="$oldSplog['words_to'] ?? ''" />
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
<div class="modal fade" id="modalDeleteRecord" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('projects.destroySplog', ['project' => ':projectId', 'splog' => ':splogId']) }}" class="d-inline-block">
                @method('DELETE')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Are you sure?') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    {{ __('Do you really want to delete these records? This process cannot be undone.') }}
                </div>
                <div class="modal-footer">
                    <x-button type="button" class="btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" class="btn-danger">{{ __('Delete') }}</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
