<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

enum DomainType: string
{
    case PRIMARY = 'primary';
    case ALIAS = 'alias';
    case REDIRECT = 'redirect';
}
