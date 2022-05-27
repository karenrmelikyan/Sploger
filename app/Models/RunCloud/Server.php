<?php

declare(strict_types=1);

namespace App\Models\RunCloud;

final class Server
{
//    {
//        "id": 65,
//        "name": "My First Server",
//        "provider": "Localhost",
//        "ipAddress": "127.0.0.1",
//        "os": "Ubuntu",
//        "osVersion": null,
//        "connected": false,
//        "online": false,
//        "agentVersion": null,
//        "phpCLIVersion": "php73rc",
//        "softwareUpdate": false,
//        "securityUpdate": true,
//        "transferStatus": "AVAILABLE",
//        "created_at": "2019-06-20 18:10:23"
//    }

    public function __construct(
        public readonly string $name,
        public readonly string $ipAddress,
        public readonly ?string $provider = null,
        public readonly ?int $id = null,
        public readonly ?string $os = null,
        public readonly ?string $osVersion = null,
        public readonly ?bool $connected = null,
        public readonly ?bool $online = null,
        public readonly ?string $agentVersion = null,
        public readonly ?string $phpCLIVersion = null,
        public readonly ?bool $softwareUpdate = null,
        public readonly ?bool $securityUpdate = null,
        public readonly ?string $transferStatus = null,
        public readonly ?string $created_at = null,
    ) {
        //
    }
}
