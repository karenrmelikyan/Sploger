<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\CacheStatus;
use App\Events\SplogDeployed;
use App\Jobs\CreateSplogPost;
use App\Models\Keyword;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\SplogRepositoryInterface;
use Exception;

use Psr\Log\LoggerInterface;

use function count;
use function random_int;
use function shuffle;

final class ScheduleSplogPosts
{
    public function __construct(
        private SplogRepositoryInterface $splogRepository,
        private ProjectRepositoryInterface $projectRepository,
        private LoggerInterface $logger,
    ) {
        //
    }

    /**
     * @throws Exception
     */
    public function handle(SplogDeployed $event): void
    {
        $domain = $event->instance->domain;
        $splog = $this->splogRepository->findByDomain($domain);
        if ($splog === null) {
            return;
        }

        $project = $this->projectRepository->findById($splog->project_id);
        if ($project === null) {
            return;
        }
        $project->load('keywordSet.keywords');

        $sections = $splog->sections_from !== null
            ? [$splog->sections_from, $splog->sections_to]
            : [$project->sections_from, $project->sections_to];
        $wordsPerSection = $splog->words_from !== null
            ? [$splog->words_from, $splog->words_to]
            : [$project->words_from, $project->words_to];
        /** @var Keyword[] $keywords */
        $keywords = $project->keywordSet->keywords->all();
        $numberOfKeywords = count($keywords);
        $this->logger->debug("Posting to $splog->domain articles for $numberOfKeywords keywords.");

        shuffle($keywords);

        $i = 0;
        $nextPostDelay = 0;
        foreach ($keywords as $keyword) {
            $i++;
            CreateSplogPost::dispatch(
                $event->instance->serverIP,
                $domain,
                $keyword,
                $keyword->language_code,
                $sections,
                $wordsPerSection,
                $project->keyword_density,
                $project->schedule_interval,
                $project->schedule_variance,
            )->delay($nextPostDelay);

            if ($project->schedule_interval !== null) {
                $nextPostDelay += $project->schedule_interval + ($project->schedule_variance === null ? 0 : random_int(
                        -1 * $project->schedule_variance,
                        $project->schedule_variance
                    ));
            }
        }
        $this->logger->debug("Dispatching $i post jobs done.");
    }
}
