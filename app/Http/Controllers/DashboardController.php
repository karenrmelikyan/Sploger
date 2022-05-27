<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\Splog;
use App\Repositories\JobRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;

use function array_map;
use function number_format;
use function response;
use function view;

final class DashboardController extends Controller
{
    public function __construct(private Connection $db, private JobRepositoryInterface $jobRepository)
    {
        //
    }

    public function index(): View
    {
        return view('dashboard.index');
    }

    public function stats(): JsonResponse
    {
        $totals = $this->db->query()
            ->selectSub(Project::selectRaw('COUNT(*)'), 'total-projects')
            ->selectSub(Splog::selectRaw('COUNT(*)'), 'total-splogs')
            ->selectSub(Keyword::selectRaw('COUNT(*)'), 'total-keywords')
            ->get()->first();

        return response()->json(['value' => (array) $totals]);
    }

    public function jobs(): JsonResponse
    {
        return response()->json(['value' => $this->jobRepository->countPending()]);
    }

    public function cache(): JsonResponse
    {
        $data = $this->db->query()
            ->from('information_schema.tables')
            ->select([
                'table_name as table',
                $this->db->raw('(data_length + index_length ) / 1024 / 1024 AS size'),
            ])
            ->where(['table_schema' => $this->db->getDatabaseName()])
            ->where(static function (Builder $query) {
                $query
                    ->where(['table_name' => 'markov_matrix'])
                    ->orWhere(['table_name' => (new Article())->getTable()]);
            })
            ->get()->pluck('size', 'table')->all();

        return response()->json(['value' => array_map(static fn (int $value) => number_format($value, 2) . 'MB', $data)]);
    }
}
