<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\Repository as Cache;
use Psr\Log\LoggerInterface;

final class WithoutOverlapping extends \Illuminate\Queue\Middleware\WithoutOverlapping
{
    public function handle($job, $next)
    {
        /** @var Lock $lock */
        $lock = Container::getInstance()->make(Cache::class)->lock(
            $this->getLockKey($job), $this->expiresAfter
        );
        /** @var LoggerInterface $logger */
        $logger = Container::getInstance()->make(LoggerInterface::class);

        if ($lock->get()) {
            $logger->debug('Lock acquired for the job.');
            try {
                $next($job);
            } finally {
                $logger->debug('Job finished. Releasing lock.');
                $lock->release();
            }
        } elseif (! is_null($this->releaseAfter)) {
            $logger->debug('Lock isn\'t acquired. Releasing job for: ' . $this->releaseAfter);
            $job->release($this->releaseAfter);
        }
    }
}
