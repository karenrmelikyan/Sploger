<?php

declare(strict_types=1);

namespace App\Models;

final class SplogInstance
{
    public int $serverRunCloudId;
    public string $serverIP;
    public string $domain;
    public int $runCloudAppId;
    public string $runCloudAppName;
    public int $userRunCloudId;
    public string $sshUser;
    public string $sshPassword;
    public int $dbRunCloudId;
    public string $dbName;
    public int $dbUserRunCloudId;
    public string $dbUser;
    public string $dbPassword;
}
