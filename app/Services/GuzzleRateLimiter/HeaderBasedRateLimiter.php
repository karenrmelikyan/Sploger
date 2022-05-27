<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function array_filter;
use function array_values;
use function count;
use function is_int;
use function is_string;
use function microtime;
use function round;
use function usleep;

class HeaderBasedRateLimiter implements RateLimiterInterface
{
    private int $windowLength;
    private int $limit;

    public function __construct(
        private string $remainingHeader,
        private string|int $quota,
        private string|int $windowSizeMs,
        private StoreInterface $store
    ) {
        if (is_int($this->windowSizeMs)) {
            $this->windowLength = (int) $this->windowSizeMs;
        }

        if (is_int($this->quota)) {
            $this->limit = (int) $this->quota;
        }
    }

    public function handle(RequestInterface $request, array $options, callable $handler): PromiseInterface
    {
        $delay = $this->getDelay();

        if ($delay > 0) {
            $this->delay($delay);
        }

        $this->store->set(
            'rate-limiter-requests',
            [...($this->store->get('rate-limiter-requests') ?? []), $this->getCurrentTime()]
        );

        return $handler($request, $options)->then(function (ResponseInterface $response) {
            if (is_string($this->windowSizeMs) && !isset($this->windowLength)) {
                $this->windowLength = (int) $response->getHeaderLine($this->windowSizeMs);
            }
            if (is_string($this->quota) && !isset($this->limit)) {
                $this->limit = (int) $response->getHeaderLine($this->quota);
            }

            $remaining = (int) $response->getHeaderLine($this->remainingHeader);
            $this->store->set('rate-limiter-remaining', $remaining);
            return $response;
        });
    }

    private function getDelay(): int
    {
        if (($this->store->get('rate-limiter-remaining') ?? 0) > 0) {
            return 0;
        }

        if (!$this->store->has('rate-limiter-requests')) {
            return 0;
        }

        $currentTimeFrameStart = $this->getCurrentTime() - $this->windowLength;
        $requestsInCurrentTimeFrame = array_values(array_filter(
            $this->store->get('rate-limiter-requests'),
            static fn (int $timestamp) => $timestamp >= $currentTimeFrameStart
        ));
        $this->store->set('rate-limiter-requests', $requestsInCurrentTimeFrame);

        if (count($requestsInCurrentTimeFrame) < $this->limit) {
            return 0;
        }

        $oldestRequestStartTimeRelativeToCurrentTimeFrame = $this->getCurrentTime() - $requestsInCurrentTimeFrame[0];

        return $this->windowLength - $oldestRequestStartTimeRelativeToCurrentTimeFrame;
    }

    private function getCurrentTime(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    private function delay(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
