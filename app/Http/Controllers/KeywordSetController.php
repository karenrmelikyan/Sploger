<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CacheStatus;
use App\Http\Requests\KeywordSet\StoreRequest;
use App\Models\Keyword;
use App\Models\KeywordSet;
use App\Repositories\KeywordRepositoryInterface;
use App\Repositories\KeywordSetRepositoryInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;
use Throwable;

use function abort;
use function array_diff;
use function array_filter;
use function array_map;
use function back;
use function count;
use function min;
use function preg_split;
use function redirect;
use function view;

final class KeywordSetController extends Controller
{
    public function __construct(
        private Connection $db,
        private KeywordSetRepositoryInterface $repository,
        private KeywordRepositoryInterface $keywordRepository
    ) {
    }

    public function index(Request $request): Renderable
    {
        $maxPerPage = 100;
        $perPage = $request->input('per-page', 25);
        $perPage = min($perPage, $maxPerPage);

        $keywordSets = $this->repository->findAllPaginated((int) $perPage);

        $filters = [];


        return view('keyword-sets.list', [
            'items' => $keywordSets,
            'filters' => $filters,
        ]);
    }

    public function create(): Renderable
    {
        return view('keyword-sets.create', [
            'languages' => require(storage_path('app/languages.php')),
        ]);
    }

    public function edit(string $id): Renderable
    {
        $set = $this->repository->findById((int) $id, true);
        if ($set === null) {
            abort(404, 'Model not found.');
        }
        $keywords = $set->keywords;

        return view('keyword-sets.edit', [
            'set' => $set,
            'languages' => require(storage_path('app/languages.php')),
            'language_code' => $keywords->random()->language_code,
            'keywords' => $keywords->implode('name', "\n"),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (isset($validated['id'])) {
            $set = $this->repository->findById((int) $validated['id'], true);
            if ($set === null) {
                abort(404, 'Model not found.');
            }
        } else {
            $set = new KeywordSet();
        }

        $keywords = array_filter(preg_split('/(\r\n|\r|\n)/', $validated['keywords']));

        $this->db->beginTransaction();
        try {
            /** @var Builder|null $removeRedundantKeywords */
            $removeRedundantKeywords = null;
            $oldKeywordIds = $set->keywords->modelKeys();
            $keywords = $this->keywordRepository->bulkCreate($keywords, $validated['language_code']);
            $newKeywordIds = array_map(static fn(Keyword $keyword) => $keyword->id, $keywords);
            $redundantKeywordIds = array_diff($oldKeywordIds, $newKeywordIds);
            if (count($redundantKeywordIds) !== 0) {
                $removeRedundantKeywords = $set->keywords
                    ->only(array_diff($oldKeywordIds, $newKeywordIds))
                    ->toQuery()
                    ->whereDoesntHave('sets');
            }
            $set->fill($validated)->save();
            $set->keywords()->sync($newKeywordIds);
            $removeRedundantKeywords?->delete();

            $this->db->commit();
        } catch (Throwable $t) {
            $this->db->rollBack();
            abort(503, $t->getMessage());
        }

        return redirect('keyword-sets');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete((int) $id);
        $this->db->statement('ANALYZE TABLE articles');
        $this->db->statement('ANALYZE TABLE markov_matrix');

        return back();
    }

    public function destroyMarkov(string $id, Request $request): RedirectResponse
    {
        $this->db
            ->query()
            ->from('markov_matrix')
            ->join('keywords', 'markov_matrix.keyword_id', '=', 'keywords.id')
            ->join('keyword_set', 'keywords.id', '=', 'keyword_set.keyword_id')
            ->where(['keyword_set.set_id' => (int) $id])
            ->delete();

        $this->db
            ->query()
            ->from('keywords')
            ->join('keyword_set', 'keywords.id', '=', 'keyword_set.keyword_id')
            ->where(['keyword_set.set_id' => (int) $id])
            ->update(['markov_cache_status' => CacheStatus::EMPTY]);

        $this->db->statement('ANALYZE TABLE markov_matrix');

        $request->session()->flash('message', __('Markov chains were removed for set #' . $id . '.'));
        return back();
    }

    public function destroyArticles(string $id, Request $request): RedirectResponse
    {
        $this->db
            ->query()
            ->from('articles')
            ->join('keywords', 'articles.keyword_id', '=', 'keywords.id')
            ->join('keyword_set', 'keywords.id', '=', 'keyword_set.keyword_id')
            ->where(['keyword_set.set_id' => (int) $id])
            ->delete();

        $this->db
            ->query()
            ->from('keywords')
            ->join('keyword_set', 'keywords.id', '=', 'keyword_set.keyword_id')
            ->where(['keyword_set.set_id' => (int) $id])
            ->update(['article_cache_status' => CacheStatus::EMPTY]);

        $this->db->statement('ANALYZE TABLE articles');

        $request->session()->flash('message', __('Fetched articles were removed for set #' . $id . '.'));
        return back();
    }
}
