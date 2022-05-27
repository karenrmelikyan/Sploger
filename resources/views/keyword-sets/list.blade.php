<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection|\App\Models\KeywordSet[]|\Illuminate\Pagination\LengthAwarePaginator $items
 * @var array $filters
 * @var array $servers
 */

use App\Models\KeywordSet;

$columns = [
    ['label' => '#', 'attribute' => 'id', 'sortable' => false,],
    ['label' => __('Name'), 'attribute' => 'name', 'sortable' => true],
    ['label' => __('Keywords'), 'attribute' => 'keywords_count', 'sortable' => true,],
    ['label' => __('Created'), 'attribute' => 'created_at', 'value' => static fn (KeywordSet $item) => $item->created_at->translatedFormat('d M Y'), 'sortable' => true],
];
$controls = [
    'buttons' => [
        'create' => [
            'url' => route('keyword-sets.create'),
            'class' => 'btn btn-primary',
            'label' => __('Create keyword set'),
            'icon' => 'bi-plus-lg',
        ],
    ],
];
$filters = [
    'name' => ['type' => 'text', 'label' => __('Name')],
    'keywords_count' => ['type' => 'text', 'label' => __('Number of keywords')],
];
$actions = [
    'keywords' => [
        'route' => [
            'name' => 'keyword-sets.keywords.index',
            'params' => static fn (KeywordSet $item) => ['keyword_set' => $item->id],
        ],
        'class' => 'btn btn-secondary',
        'label' => __('View keywords'),
        'icon' => 'bi-list',
    ],
    'destroy-articles' => [
        'route' => [
            'name' => 'keyword-sets.destroy-articles',
            'method' => 'delete',
            'params' => static fn (KeywordSet $item) => ['set' => $item->id],
        ],
        'class' => 'btn btn-warning',
        'label' => __('Delete articles cache'),
        'icon' => 'bi-x-circle',
        'confirm' => __('Are you sure you want to remove article cache? The process cannot be undone.'),
    ],
    'destroy-markov' => [
        'route' => [
            'name' => 'keyword-sets.destroy-markov',
            'method' => 'delete',
            'params' => static fn (KeywordSet $item) => ['set' => $item->id]
        ],
        'class' => 'btn btn-warning',
        'label' => __('Delete Markov cache'),
        'icon' => 'bi-x-square',
        'confirm' => __('Are you sure you want to remove markov cache? The process cannot be undone.'),
    ],
    'edit' => [
        'route' => [
            'name' => 'keyword-sets.edit',
            'params' => static fn (KeywordSet $item) => ['set' => $item->id],
        ],
        'class' => 'btn btn-success',
        'label' => __('Edit keyword set'),
        'icon' => 'bi-pencil',
    ],
    'destroy' => [
        'route' => [
            'name' => 'keyword-sets.destroy',
            'method' => 'delete',
            'params' => static fn (KeywordSet $item) => ['set' => $item->id],
        ],
        'class' => 'btn btn-danger',
        'label' => __('Delete keyword set'),
        'icon' => 'bi-trash',
        'confirm' => __('Are you sure you want to delete this record? The process cannot be undone.'),
    ],
];
?>
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Keyword Sets') }}</x-slot>
    @if (session()->exists('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <x-data.grid :paginator="$items" :columns="$columns" :filters="$filters" :actions="$actions" :controls="$controls" />
</x-app-layout>
