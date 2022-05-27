<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CacheStatus;
use App\Jobs\CrawlBingUrlsForKeyword;
use App\Jobs\GenerateMarkovTable;
use App\Models\Article;
use App\Models\Keyword;
use App\Repositories\KeywordRepositoryInterface;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

use function back;
use function min;
use function view;

final class KeywordController extends Controller
{
    public function __construct(
        private Connection $db,
        private KeywordRepositoryInterface $repository,
        private Dispatcher $dispatcher,
    ) {
        //
    }

    public function index(int $keywordSet, Request $request): View
    {
        $maxPerPage = 100;
        $perPage = $request->input('per-page', 25);
        $perPage = min($perPage, $maxPerPage);

        $keywords = $this->repository->findBySet($keywordSet, $perPage, true);

        $filters = [];


        return view('keywords.list', [
            'items' => $keywords,
            'filters' => $filters,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete((int) $id);
        return back();
    }

    public function destroyMarkovCache(string $id): RedirectResponse
    {
        $this->db->query()
            ->from('markov_matrix')
            ->where(['keyword_id' => (int) $id])
            ->delete();

        Keyword::where(['id' => (int) $id])->update(['markov_cache_status' => CacheStatus::EMPTY]);
        $this->db->statement('ANALYZE TABLE markov_matrix');

        return back();
    }

    public function destroyArticlesCache(string $id): RedirectResponse
    {
        (new Article())
            ->where(['keyword_id' => (int) $id])
            ->delete();

        Keyword::where(['id' => (int) $id])->update(['article_cache_status' => CacheStatus::EMPTY]);
        $this->db->statement('ANALYZE TABLE articles');

        return back();
    }

    public function regenerateMarkov(string $id, Request $request): RedirectResponse
    {
        $this->db->table('markov_matrix')->where([
            'keyword_id' => $id,
        ])->delete();
        $keyword = $this->repository->findById((int) $id);
        if ($keyword === null) {
            throw new RuntimeException('Unknown keyword');
        }
        $keyword->markov_cache_status = CacheStatus::PENDING;
        $keyword->save();
        $this->dispatch(new GenerateMarkovTable($keyword->name, $keyword->language_code));
        $request->session()->flash('message', __('Markov chains regeneration is initialized. It will be done in background.'));
        return back();
    }

    public function fetchArticlesRegenerateMarkov(string $id, Request $request): RedirectResponse
    {
        $this->db->table('markov_matrix')->where([
            'keyword_id' => $id,
        ])->delete();
        $this->db->table('articles')->where([
            'keyword_id' => $id,
        ])->delete();
        $keyword = $this->repository->findById((int) $id);
        if ($keyword === null) {
            throw new RuntimeException('Unknown keyword');
        }
        $keyword->article_cache_status = CacheStatus::PENDING;
        $keyword->markov_cache_status = CacheStatus::PENDING;
        $keyword->save();
        $this->dispatcher->chain([
            new CrawlBingUrlsForKeyword($keyword->name, $keyword->language_code),
            new GenerateMarkovTable($keyword->name, $keyword->language_code),
        ])->dispatch();
        $request->session()->flash('message', __('Articles will be fetched again and Markov chains generated. It will be done in background.'));
        return back();
    }
}
