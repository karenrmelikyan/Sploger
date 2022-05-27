<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class WebApplication
{
    public ?int $id = null;
    public string $name;
    public string $domainName;
    public int $user;
    public string $phpVersion = 'php72rc';
    public string $stack = 'nativenginx';
    public string $stackMode = 'production';
    public bool $clickjackingProtection = true;
    public bool $xssProtection = true;
    public bool $mimeSniffingProtection = true;
    public string $processManager = 'ondemand';
    public int $processManagerMaxChildren = 50;
    public int $processManagerMaxRequests = 500;
    public string $timezone = 'UTC';
    public int $maxExecutionTime = 30;
    public int $maxInputTime = 60;
    public int $maxInputVars = 1000;
    public int $memoryLimit = 256;
    public int $postMaxSize = 256;
    public int $uploadMaxFilesize = 256;
    public int $sessionGcMaxlifetime = 1440;
    public bool $allowUrlFopen = true;
    public ?DateTimeImmutable $createdAt = null;

    public function __construct(string $name, string $domainName, int $user)
    {
        $this->name = $name;
        $this->domainName = $domainName;
        $this->user = $user;
    }
}
