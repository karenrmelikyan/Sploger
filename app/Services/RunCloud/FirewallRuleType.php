<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

enum FirewallRuleType: string
{
    case GLOBAL = 'global';
    case RICH = 'rich';
}
