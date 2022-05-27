<?php
/**
 * @var \App\Models\RunCloud\Server[] $servers
 */
?>
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Servers') }}</x-slot>
    <table class="table table-striped table-hover align-middle">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">{{ __('Name') }}</th>
            <th scope="col">{{ __('Provider') }}</th>
            <th scope="col">{{ __('IP Address') }}</th>
            <th scope="col">{{ __('OS') }}</th>
            <th scope="col">{{ __('OS Version') }}</th>
            <th scope="col">{{ __('Connected') }}</th>
            <th scope="col">{{ __('Online') }}</th>
            <th scope="col">{{ __('Created At') }}</th>
            <th scope="col">{{ __('Firewall') }}</th>
            <th class="actions" scope="col">{{ __('Actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($servers as $server)
            <tr>
                <th scope="row">{{ $server->id }}</th>
                <td>{{ $server->name }}</td>
                <td>{{ $server->provider }}</td>
                <td>{{ $server->ipAddress }}</td>
                <td>{{ $server->os }}</td>
                <td>{{ $server->osVersion }}</td>
                <td class="text-center"><x-status :value="$server->connected" /></td>
                <td class="text-center"><x-status :value="$server->online" /></td>
                <td>{{ $server->created_at }}</td>
                <td><div class="fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/servers/firewall-status', ['id' => $server->id]) !!}" data-xhr-value><span class="placeholder d-block">&nbsp;</span></div></td>
                <th class="actions" scope="row">
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('servers.firewall-required-rules', ['id' => $server->id]) }}" class="btn btn-secondary disabled xhr-magic" data-xhr="{!! route('api/servers/firewall-status', ['id' => $server->id]) !!}" data-xhr-class role="button" title="{{ __('Fix firewall rules') }}" aria-label="{{ __('Fix firewall rules') }}">
                            <i class="bi-lightbulb" aria-hidden="true"></i>
                        </a>
                    </div>
                </th>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-app-layout>
