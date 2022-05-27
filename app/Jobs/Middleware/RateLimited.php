<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use App\Cache\RateLimiter;
use Illuminate\Container\Container;
use Psr\Log\LoggerInterface;

final class RateLimited extends \Illuminate\Queue\Middleware\RateLimited
{
//    /**
//     * Create a new middleware instance.
//     *
//     * @param  string  $limiterName
//     * @return void
//     */
//    public function __construct($limiterName)
//    {
//        $this->limiter = Container::getInstance()->make(RateLimiter::class);
//        $this->limiterName = $limiterName;
//    }

    /**
     * Handle a rate limited job.
     *
     * @param  callable  $next
     */
    protected function handleJob(mixed $job, $next, array $limits): mixed
    {
        /** @var LoggerInterface $logger */
        $logger = Container::getInstance()->make(LoggerInterface::class);

        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                $logger->debug('Rate limiting applied to the server. Releasing job for: ' . $this->getTimeUntilNextRetry($limit->key));
                return $this->shouldRelease
                    ? $job->release($this->getTimeUntilNextRetry($limit->key))
                    : false;
            }

            $hits = $this->limiter->hit($limit->key, $limit->decayMinutes * 60);
            $logger->debug('RateLimiter hits increased to: ' . $hits);
        }

        return $next($job);
    }
}
