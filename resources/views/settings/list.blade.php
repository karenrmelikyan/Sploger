@php
/**
 * @var \App\Models\Setting[] $settings
 */
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Settings') }}</x-slot>
    <table class="table table-striped table-hover align-middle">
        <thead>
        <tr>
            <th scope="col">{{ __('Name') }}</th>
            <th scope="col" style="width: 110px;">{{ __('Actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($settings as $setting)
            <tr>
                <td>{{ $setting->name }}</td>
                <td class="actions">
                    <a href="{{ route('settings.edit', ['setting' => $setting->id]) }}" class="btn btn-success" aria-label="{{ __('Update settings') }}">
                        <span aria-hidden="true" class="bi-pencil"></span>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-app-layout>
