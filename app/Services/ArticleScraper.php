<?php

declare(strict_types=1);

namespace App\Services;

use andreskrey\Readability\Configuration;
use andreskrey\Readability\Readability;
use Psr\Http\Client\ClientExceptionInterface;

use function preg_replace;
use function trim;

final class ArticleScraper
{
    public function __construct(private GreedyProxyCrawler $crawler)
    {
        //
    }

    // https://github.com/crscheid/php-article-extractor
    // https://github.com/andreskrey/readability.php
    // https://github.com/scotteh/php-goose
    public function extract(string $url): ?string
    {
        try {
            $response = $this->crawler->get($url);
            $config = new Configuration([
                'SummonCthulhu' => true,
                'ArticleByLine' => true,
            ]);
            $readability = new Readability($config);
            $readability->parse($response);
        } catch (\Exception) {
            return null;
        }

        $content = trim(preg_replace([
            '/<[^<]+?>|(?<=\s)\s+|&#[^;]+;|\t+/',
            '/\n+/'
        ], [
            '',
            ' ',
        ], $readability->getContent()));

        return $content !== '' ? $content : null;
    }
}
