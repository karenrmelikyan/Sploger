<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProjectDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Project $project)
    {
        //
    }
}
