<?php

namespace App\Jobs;

use App\Events\SplogInstanceCreated;
use App\Models\SplogInstance;
use App\Models\WebApplication;
use App\Services\RunCloud\DomainType;
use App\Services\RunCloud\WebApplicationService;
use App\Services\RunCloudService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Str;

use Throwable;

use function random_int;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

class CreateSplogInstance implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    public function __construct(private string $domain, private int $serverId)
    {
        $this->onQueue('splog-deployer');
    }

    /**
     * @throws Exception
     */
    public function handle(RunCloudService $runcloud, WebApplicationService $webAppService, Dispatcher $dispatcher): void
    {
        $instance = new SplogInstance();
        $instance->serverRunCloudId = $this->serverId;
        $instance->serverIP = $runcloud->getServerIpAddress($instance->serverRunCloudId);
        $instance->domain = $this->domain;
        $domainAlias = str_starts_with($this->domain, 'www') ? substr($this->domain, 4) : "www.$this->domain";
        $instance->runCloudAppName = $this->getApplicationName();
        $instance->sshUser = $this->getUsername();
        $instance->sshPassword = $this->getUserPassword();
        $instance->dbName = $this->getDBName();
        $instance->dbUser = $this->getDBUsername();
        $instance->dbPassword = $this->getDBUserPassword();

        // TODO: Handle errors, and remove unfinished deployments
        try {
            $instance->userRunCloudId = $runcloud->createSystemUser($instance->serverRunCloudId, $instance->sshUser, $instance->sshPassword);
            $instance->dbRunCloudId = $runcloud->createDatabase($instance->serverRunCloudId, $instance->dbName);
            $instance->dbUserRunCloudId = $runcloud->createDatabaseUser($instance->serverRunCloudId, $instance->dbUser, $instance->dbPassword);
            $runcloud->attachUserToDb($instance->serverRunCloudId, $instance->dbRunCloudId, $instance->dbUserRunCloudId);
            $webApp = $runcloud->createWebApplication(
                $instance->serverRunCloudId,
                new WebApplication($instance->runCloudAppName, $instance->domain, $instance->userRunCloudId)
            );
            if ($webApp->id !== null) {
                $instance->runCloudAppId = $webApp->id;
                $webAppService->addDomainName($this->serverId, $webApp->id, $domainAlias, DomainType::REDIRECT);
                $runcloud->installPHPScript($instance->serverRunCloudId, $instance->runCloudAppId, 'wordpress');

                $dispatcher->dispatch(new SplogInstanceCreated($instance));
            }
        } catch (Throwable $e) {
            // (string) $e->getResponse()->getBody()
            if (isset($instance->userRunCloudId)) {
                $runcloud->deleteSystemUser($instance->serverRunCloudId, $instance->userRunCloudId);
            }
            if (isset($instance->dbRunCloudId)) {
                $runcloud->deleteDatabase($instance->serverRunCloudId, $instance->dbRunCloudId, false);
            }
            if (isset($instance->dbUserRunCloudId)) {
                $runcloud->deleteDatabaseUser($instance->serverRunCloudId, $instance->dbUserRunCloudId);
            }
            if (isset($webApp) && $webApp->id !== null) {
                $runcloud->deleteWebApplication($instance->serverRunCloudId, $webApp->id);
            }

            $this->release(180);
        }
    }

    public function displayName(): string
    {
        return 'Creating splog instance for "' . $this->domain . '" on server id: ' . $this->serverId;
    }

    /**
     * @throws Exception
     */
    private function getUsername(): string
    {
        // length must be less than 32 characters
        // ^[a-z_]([a-z0-9_-]{0,31}|[a-z0-9_-]{0,30}\$)$

        $validFirstCharacter = 'abcdefghijklmnopqrstuvwxyz_';
        $firstCharacter = $validFirstCharacter[random_int(0, strlen($validFirstCharacter) - 1)];
        $validNotFirstCharacter = 'abcdefghijklmnopqrstuvwxyz0123456789';

        $username = $firstCharacter;
        for ($i = 1; $i < 32; $i++) {
            $username .= $validNotFirstCharacter[random_int(0, strlen($validNotFirstCharacter) - 1)];
        }

        return $username;
    }

    private function getUserPassword(): string
    {
        return Str::random();
    }

    private function getDBName(): string
    {
        // must be less than 24 characters (RunCloud limit?)
        return Str::random();
    }

    private function getDBUsername(): string
    {
        // must be less than 24 characters (RunCloud limit?)
        return Str::random();
    }

    private function getDBUserPassword(): string
    {
        return Str::random();
    }

    private function getApplicationName(): string
    {
        $name = str_replace(['.', '-'], '_', $this->domain);
        // RunCloud API has 30 characters limit on web application name
        if (strlen($name) > 30) {
            return substr($name, 0, 26) . Str::random(4);
        }

        return $name;
    }
}
