@php
/**
 * @var \Illuminate\Database\Eloquent\Collection|\App\Models\Splog[]|\Illuminate\Pagination\LengthAwarePaginator $items
 * @var array $filters
 * @var array $servers
 */

use App\Models\Splog;

$columns = [
    ['label' => '#', 'attribute' => 'id', 'sortable' => false,],
    ['label' => __('Domain'), 'attribute' => 'domain', 'value' => static fn (Splog $item) => '<a href="http://' . $item->domain . '" target="_blank">' . $item->domain . '</a>', 'sortable' => true],
    ['label' => __('Project'), 'attribute' => 'project.name', 'sortable' => true, 'value' => static fn (Splog $item) => '(' . $item->project->id . ') ' . $item->project->name],
    ['label' => __('Server'), 'attribute' => 'server_id', 'value' => static fn (Splog $item) => $item->server_id ? $servers[$item->server_id] : $servers[$item->project->server_id], 'sortable' => false],
    ['label' => __('Sections'), 'attribute' => 'sections', 'value' => static fn (Splog $item) => ($item->sections_from ?? $item->project->sections_from) . ' - ' . ($item->sections_to ?? $item->project->sections_to), 'sortable' => false],
    ['label' => __('Words per section'), 'attribute' => 'words_per_section', 'value' => static fn (Splog $item) => ($item->words_from ?? $item->project->words_from) . ' - ' . ($item->words_to ?? $item->project->words_to), 'sortable' => false],
    ['label' => __('Status'), 'attribute' => 'instance_status', 'value' => static fn (Splog $item) => '<span class="bi-circle-fill" style="color: ' . $item->getStatusColor() .'"></span>', 'sortable' => false],
    ['label' => __('Created'), 'attribute' => 'created_at', 'value' => static fn (Splog $item) => $item->created_at->translatedFormat('d M Y'), 'sortable' => true],
];
$filters = [
    'domain' => ['type' => 'text', 'label' => __('Domain')],
    'project.name' => ['type' => 'select', 'attribute' => 'project_id', 'values' => $filters['project_id'], 'label' => __('All projects')],
    'instance_status' => ['type' => 'select', 'values' => $filters['instance_status'], 'label' => __('All statuses')],
];
$actions = [
    'destroy' => [
        'route' => [
            'name' => 'splogs.destroy',
            'method' => 'delete',
            'params' => static fn (Splog $item) => ['splog' => $item->id],
        ],
        'class' => 'btn btn-danger',
        'label' => __('Delete splog'),
        'icon' => 'bi-trash',
        'confirm' => __('Are you sure you want to delete this record? The process cannot be undone.'),
    ],
];
@endphp
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Splogs') }}</x-slot>
    <x-data.grid :paginator="$items" :columns="$columns" :filters="$filters" :actions="$actions" />
</x-app-layout>
