<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection|\App\Models\KeywordSet[]|\Illuminate\Pagination\LengthAwarePaginator $items
 * @var array $filters
 */

use App\Enums\CacheStatus;
use App\Models\Keyword;

$columns = [
    ['label' => '#', 'attribute' => 'id', 'sortable' => false,],
    ['label' => __('Name'), 'attribute' => 'name', 'sortable' => true],
    ['label' => __('Markov Generated'), 'attribute' => 'markov_distinct_tokens', 'value' => static function (Keyword $item) {
        return ($item['markov_tokens'] !== null && $item['markov_tokens'] !== 0) ? __('Yes') : __('No');
    }],
    ['label' => __('Language'), 'attribute' => 'language_code', 'sortable' => true],
    ['label' => __('Articles'), 'attribute' => 'articles.count', 'sortable' => true, 'value' => static fn (Keyword $item) => $item['articles_count'] ?? 0],
    ['label' => __('Markov Quality'), 'attribute' => 'markov_tokens', 'value' => static fn (Keyword $item) => ($item['markov_distinct_tokens'] === null || $item['markov_distinct_tokens'] === 0) ? '-' : number_format($item['markov_distinct_tokens'] / $item['markov_tokens'] * 100, 2) . '%'],
];
$controls = [
//    'buttons' => [
//        'create' => [
//            'url' => route('keyword-sets.create'),
//            'class' => 'btn btn-primary',
//            'label' => __('Create keyword set'),
//            'icon' => 'bi-plus-lg',
//        ],
//    ],
];
$filters = [
    'name' => ['type' => 'text', 'label' => __('Name')],
    //'keywords_count' => ['type' => 'text', 'label' => __('Number of keywords')],
];
$actions = [
    'regenerate-markov' => [
        'route' => [
            'name' => 'keywords.regenerate-markov',
            'params' => static fn (Keyword $item) => [
                'keyword' => $item->id,
            ],
        ],
        'disabled' => static fn(Keyword $item
        ) => $item['article_cache_status'] !== CacheStatus::CACHED || $item['markov_cache_status'] === CacheStatus::PENDING || $item['articles_count'] === 0,
        'class' => 'btn btn-info',
        'label' => __('Regenerate Markov chains'),
        'icon' => 'bi-arrow-clockwise',
    ],
    'regenerate-cache' => [
        'route' => [
            'name' => 'keywords.regenerate-cache',
            'params' => static fn (Keyword $item) => [
                'keyword' => $item->id,
            ],
        ],
        'disabled' => static fn (Keyword $item) => $item['article_cache_status'] === CacheStatus::PENDING,
        'class' => 'btn btn-success',
        'label' => __('Fetch articles and regenerate Markov chains'),
        'icon' => 'bi-arrow-repeat',
    ],
    'destroy-articles' => [
        'route' => [
            'name' => 'keywords.destroy-articles',
            'method' => 'delete',
            'params' => static fn (Keyword $item) => [
                'keyword' => $item->id,
            ],
        ],
        'class' => 'btn btn-warning',
        'disabled' => static fn (Keyword $item) => $item['article_cache_status'] !== CacheStatus::CACHED,
        'label' => __('Delete articles cache'),
        'icon' => 'bi-x-circle',
        'confirm' => __('Are you sure you want to remove article cache? The process cannot be undone.'),
    ],
    'destroy-markov' => [
        'route' => [
            'name' => 'keywords.destroy-markov',
            'method' => 'delete',
            'params' => static fn (Keyword $item) => [
                'keyword' => $item->id,
            ],
        ],
        'class' => 'btn btn-warning',
        'disabled' => static fn (Keyword $item) => $item['markov_cache_status'] !== CacheStatus::CACHED,
        'label' => __('Delete Markov cache'),
        'icon' => 'bi-x-square',
        'confirm' => __('Are you sure you want to remove markov cache? The process cannot be undone.'),
    ],
    'destroy' => [
        'route' => [
            'name' => 'keywords.destroy',
            'method' => 'delete',
            'params' => static fn (Keyword $item) => ['keyword' => $item->id],
        ],
        'class' => 'btn btn-danger',
        'label' => __('Delete keyword'),
        'icon' => 'bi-trash',
        'confirm' => __('Are you sure you want to delete this record? The process cannot be undone.'),
    ],
];
?>
<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">{{ __('Keywords') }}</x-slot>
    @if (session()->exists('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <x-data.grid :paginator="$items" :columns="$columns" :filters="$filters" :actions="$actions" :controls="$controls" />
</x-app-layout>
