<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

enum Protocol: string
{
    case TCP = 'tcp';
    case UDP = 'udp';
}
