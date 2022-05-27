@props(['column', 'title'])
@php
/**
* @var \Illuminate\View\ComponentAttributeBag $attributes
* @var \Illuminate\Support\HtmlString $slot
* @var string $title
* @var string $column
*/

use Illuminate\Support\Arr;

$question = request()->getBaseUrl() . request()->getPathInfo() === '/' ? '/?' : '?';
$query = clone request()->query;
$direction = 'asc';
$icon = 'bi-arrow-down-up';
$isSorted = $query->has('sort') && $query->get('sort') === $column;
if ($isSorted && $query->has('direction')) {
    $direction = $query->get('direction');
    $query->set('direction', $direction === 'asc' ? 'desc' : 'asc');
    $icon = $direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up';
} else {
    $query->add(['direction' => $direction]);
}
$query->set('sort', $column);
$query->remove('page');
$url = count($query) > 0 ? request()->url() . $question . Arr::query($query->all()) : request()->fullUrl();
@endphp
<a class="d-flex align-items-center justify-content-between text-nowrap link-dark text-decoration-none" href="{{ $url }}">{{ $title }}&nbsp;<small class="{{ $icon }}"></small></a>
