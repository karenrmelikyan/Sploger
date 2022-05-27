<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreRequest;
use App\Models\KeywordSet;
use App\Models\RunCloud\Server;
use App\Repositories\KeywordSetRepositoryInterface;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\ServerRepositoryInterface;
use App\Repositories\SplogRepositoryInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function abort;
use function back;
use function collect;
use function redirect;
use function storage_path;
use function view;

final class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private KeywordSetRepositoryInterface $keywordSetRepository,
        private ServerRepositoryInterface $serverRepository,
        private SplogRepositoryInterface $splogRepository,
    ) {
    }

    public function index(Request $request): Renderable
    {
        $maxPerPage = 100;
        $perPage = $request->input('per-page', 25);
        $perPage = min($perPage, $maxPerPage);

        $projects = $this->repository->findAllPaginated((int) $perPage, true, true);
        $filters = [
            'keyword_set_id' => KeywordSet::filterable(['keyword_set_id'])->select(['id', 'name'])->pluck('name', 'id')->toArray(),
        ];

        return view('project.list', [
            'items' => $projects,
            'filters' => $filters,
        ]);
    }

    public function create(): Renderable
    {
        $keywordSets = $this->keywordSetRepository
            ->findAll()
            ->mapWithKeys(static fn (KeywordSet $item) => [$item->id => $item->name]);
        $servers = collect($this->serverRepository->listServers())
            ->mapWithKeys(static fn (Server $item) => [$item->id => $item->name]);

        return view('project.create', [
            'keywordSets' => $keywordSets,
            'servers' => $servers,
        ]);
    }

    public function edit(string $id): Renderable
    {
        $model = $this->repository->findById((int) $id, true);
        if ($model === null) {
            abort(404, 'Model not found.');
        }

        $keywordSets = $this->keywordSetRepository
            ->findAll()
            ->mapWithKeys(static fn (KeywordSet $item) => [$item->id => $item->name]);
        $servers = collect($this->serverRepository->listServers())
            ->mapWithKeys(static fn (Server $item) => [$item->id => $item->name]);

        return view('project.edit', [
            'project' => $model,
            'keywordSets' => $keywordSets,
            'servers' => $servers,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        if (isset($request->validated()['id'])) {
            $this->repository->updateFromRequest($request);
        } else {
            $this->repository->createFromRequest($request);
        }
        return redirect('projects');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete((int) $id);
        return back();
    }

    public function destroySplog(string $project_id, string $splog_id): RedirectResponse
    {
        $this->splogRepository->delete((int) $splog_id);
        return back();
    }
}
