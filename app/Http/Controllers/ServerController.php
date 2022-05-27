<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\ServerRepositoryInterface;
use App\Services\RunCloud\SecurityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

use function back;
use function response;
use function view;

class ServerController extends Controller
{
    public function __construct(private ServerRepositoryInterface $repository, private SecurityService $runCloudSecurity)
    {
    }

    public function index(): View
    {
        $servers = $this->repository->listServers();

        return view('server.list', ['servers' => $servers]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function firewallStatus(int $serverId): JsonResponse
    {
        $hasMissingRules = $this->runCloudSecurity->hasMissingRequiredRules($serverId);
        $color = $hasMissingRules ? '#ee0000' : '#008800';

        return response()->json([
            'value' => '<span class="bi-circle-fill" style="color: ' . $color . '"></span>',
            'class' => ['disabled' => !$hasMissingRules, 'btn-success' => $hasMissingRules, 'btn-secondary' => !$hasMissingRules],
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function addRequiredFirewallRules(int $serverId): RedirectResponse
    {
        if ($this->runCloudSecurity->hasMissingRequiredRules($serverId)) {
            $this->runCloudSecurity->deployRequiredRules($serverId);
        }

        return back();
    }
}
