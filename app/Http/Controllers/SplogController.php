<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RunCloud\Server;
use App\Models\Splog;
use App\Repositories\ServerRepositoryInterface;
use App\Repositories\SplogRepositoryInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function back;
use function collect;
use function view;

final class SplogController extends Controller
{
    public function __construct(private SplogRepositoryInterface $repository, private ServerRepositoryInterface $serverRepository)
    {
    }

    public function index(Request $request): Renderable
    {
        $maxPerPage = 100;
        $perPage = $request->input('per-page', 25);
        $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

        $splogs = $this->repository->findAllPaginated((int) $perPage, true);

        $filters = [
            'project_id' => Project::filterable(['project_id'])->select(['id', 'name'])->pluck('name', 'id')->toArray(),
            'instance_status' => Splog::statuses(),
        ];

        $servers = collect($this->serverRepository->listServers())
            ->mapWithKeys(static fn (Server $item) => [$item->id => $item->name]);

        return view('splog.list', [
            'items' => $splogs,
            'filters' => $filters,
            'servers' => $servers,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete((int) $id);
        return back();
    }
}
