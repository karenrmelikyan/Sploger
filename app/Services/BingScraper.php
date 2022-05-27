<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

use function json_decode;

final class BingScraper
{
    private const BING_SEARCH_URL = 'https://www.bing.com/search';
    private const BING_IMAGE_SEARCH_URL = 'https://www.bing.com/images/search';

    public function __construct(private GreedyProxyCrawler $crawler)
    {
        //
    }

    public function getResults(string $keyword, string $languageCode, int $page = 1): array
    {
        $query = [
            'q' => "$keyword language:$languageCode",
            'first' => 10 * ($page - 1) + 1,
        ];

        $response = $this->crawler->get(self::BING_SEARCH_URL, $query);

        $results = [];
        $crawler = new Crawler($response);

        $crawler
            ->filterXPath('//*[@id="b_results"]/*[@class="b_algo"]')
            ->each(static function (Crawler $node) use (&$results) {
                $descriptionNode = $node->filterXPath("//*[contains(concat(' ',normalize-space(@class),' '),' b_caption ')]//p");
                $results[] = [
                    'title' => $node->filterXPath('//h2/a')->text(),
                    'url' => $node->filterXPath('//h2/a')->link()->getUri(),
                    'description' => $descriptionNode->count() !== 0 ? $descriptionNode->text() : '',
                ];
            });

        return $results;
    }

    public function getImageResults(string $keyword, string $languageCode): array
    {
        $query = [
            'q' => "$keyword language:$languageCode",
            'first' => 1,
            // Disable BING safe search filter
            'safesearch' => 'off'
        ];

        $response = $this->crawler->get(self::BING_IMAGE_SEARCH_URL, $query);

        $results = [];
        $crawler = new Crawler($response);

        $crawler
            ->filterXPath("//*[contains(concat(' ',normalize-space(@class),' '),' imgpt ')]/a")
            ->each(static function (Crawler $node) use (&$results) {
                $params = json_decode($node->attr('m'), true, flags: JSON_THROW_ON_ERROR);
                $results[] = $params['murl'];
            });

        return $results;
    }
}
