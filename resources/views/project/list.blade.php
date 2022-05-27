<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection|\App\Models\Project[]|\Illuminate\Pagination\LengthAwarePaginator $items
 * @var array $filters
 */

use App\Models\Project;

$columns = [
    ['label' => '#', 'attribute' => 'id', 'sortable' => false,],
    ['label' => __('Name'), 'attribute' => 'name', 'sortable' => true],
    ['label' => __('Keyword Set'), 'attribute' => 'keywordSet.name', 'sortable' => true,],
    ['label' => __('Splogs'), 'attribute' => 'splogs_count', 'sortable' => true, 'value' => static function (Project $item) {
        return '<a href="' . route('splogs.index', ['filter[project_id]' => '=' . $item->id]) . '">' . $item->splogs_count . '</a>';
    }],
    ['label' => __('Created'), 'attribute' => 'created_at', 'value' => static fn (Project $item) => $item->created_at->translatedFormat('d M Y'), 'sortable' => true],
];
$controls = [
    'buttons' => [
        'create' => [
            'url' => route('projects.create'),
            'class' => 'btn btn-primary',
            'label' => __('Create project'),
            'icon' => 'bi-plus-lg',
        ],
    ],
];
$filters = [
    'name' => ['type' => 'text', 'label' => __('Name')],
    'keywordSet.name' => ['type' => 'select', 'attribute' => 'keyword_set_id', 'values' => $filters['keyword_set_id'], 'label' => __('All sets')],
    'splogs_count' => ['type' => 'text', 'label' => __('Number of splogs')],
];
$actions = [
    'edit' => [
        'route' => [
            'name' => 'projects.edit',
            'params' => static fn (Project $item) => ['project' => $item->id],
        ],
        'class' => 'btn btn-success',
        'label' => __('Edit project'),
        'icon' => 'bi-pencil',
    ],
    'destroy' => [
        'route' => [
            'name' => 'projects.destroy',
            'method' => 'delete',
            'params' => static fn (Project $item) => ['project' => $item->id],
        ],
        'class' => 'btn btn-danger',
        'label' => __('Delete project'),
        'icon' => 'bi-trash',
        'confirm' => __('Are you sure you want to delete this record? The process cannot be undone.'),
    ],
];
?>
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Projects') }}</x-slot>
    <x-data.grid :paginator="$items" :columns="$columns" :filters="$filters" :actions="$actions" :controls="$controls" />
</x-app-layout>
