<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use InvalidArgumentException;

use JetBrains\PhpStorm\ArrayShape;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_rand;
use function array_reduce;
use function array_slice;
use function array_splice;
use function array_sum;
use function array_unique;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_replace;
use function random_int;
use function rtrim;
use function trim;
use function ucfirst;

// TODO: check https://github.com/jsvine/markovify
final class MarkovChain
{
    private const PUNCTUATIONS = ['.', '-', ',', ':', '"', '!', '¡', '?', '¿', '(', '—', ')'];

    public static function generateMarkovMatrix(string $corpus, int $lookForward = 2): array
    {
        if ($lookForward < 1) {
            throw new InvalidArgumentException("Look forward can't be less than 1.");
        }

        $tokens = self::tokenize($corpus)['tokens'];

        $dictionary = [];
        for ($i = 0; $i < count($tokens) - $lookForward; $i++) {
            $tokenSequence = implode(' ', array_slice($tokens, $i, $lookForward));
            if (!array_key_exists($tokenSequence, $dictionary)) {
                $dictionary[$tokenSequence] = [];
            }
            $nextToken = $tokens[$i + $lookForward];

            if (array_key_exists($nextToken, $dictionary[$tokenSequence])) {
                ++$dictionary[$tokenSequence][$nextToken];
            } else {
                $dictionary[$tokenSequence][$nextToken] = 1;
            }
        }

        return $dictionary;
    }

    /**
     * @throws Exception
     */
    public static function generateMarkovSections(
        array $sections,
        array $wordsPerSection,
        array $matrix,
        string $keyword,
        int $keyword_density = null,
        array $internalLinks = [],
    ): string {
        $numberOfSections = random_int($sections[0], $sections[1]);
        $currentSection = 1;
        $text = '';

        while ($currentSection <= $numberOfSections) {
            $length = random_int($wordsPerSection[0], $wordsPerSection[1]);
            $text .= '<h2>' . self::generateMarkovHeading(random_int(1, 4), $matrix) . '</h2>';
            $content = self::generateMarkovChain($length, $matrix);
            if ($keyword_density !== null) {
                $content = self::addKeywordToText($content, $keyword, (int) ($length * $keyword_density/100));
            }
            foreach ($internalLinks as $internalLink) {
                $content = self::addTextToContent($content, $internalLink);
            }
            $text .= '<p>' . $content . '</p>';
            $currentSection++;
        }

        return $text;
    }

    /**
     * @throws Exception
     */
    private static function addKeywordToText(string $text, string $keyword, int $numberOfTimes): string
    {
        // check current density
        $numberOfKeywordMatches = preg_match_all('/' . preg_quote($keyword, '/') . '/i', $text);
        if ($numberOfKeywordMatches >= $numberOfTimes) {
            // we already have required density
            return $text;
        }

        // convert the string into array
        $words = explode(' ', $text);
        $length = count($words);
        $remainingTimes = $numberOfTimes - $numberOfKeywordMatches;
        while ($remainingTimes > 0) {
            $randomPosition = random_int(0, $length);
            array_splice($words, $randomPosition, 0, explode(' ', $keyword));
            $remainingTimes--;
        }

        return implode(' ', $words);
    }

    /**
     * @throws Exception
     */
    public static function addTextToContent(string $content, string $text): string
    {
        // convert the string into array
        $words = explode(' ', $content);
        $length = count($words);
        $randomPosition = random_int(0, $length);
        array_splice($words, $randomPosition, 0, explode(' ', $text));

        return implode(' ', $words);
    }

    public static function generateMarkovHeading(int $length, array $matrix): string
    {
        $startingToken = self::pickRandomStartingToken($matrix);
        $text = ucfirst($startingToken);
        // We can count number of words at the beginning this way
        // as long as we are sure we don't have punctuation marks there
        // which is checked for starting token anyways.
        $numberOfWords = count(explode(' ', $startingToken));

        while (true) {
            // We can encounter this situation for the last token
            if (!array_key_exists($startingToken, $matrix)) {
                $startingToken = self::pickRandomStartingToken($matrix);
            }
            $newToken = self::weightedChoice($matrix[$startingToken]);
            $text .= ' ' . $newToken;
            if (self::isWord($newToken)) {
                $numberOfWords++;
            }
            $shiftedToken = implode(' ', array_slice(explode(' ', $startingToken), 1));
            $startingToken = $shiftedToken !== '' ? $shiftedToken . ' ' . $newToken : $newToken;

            if ($numberOfWords >= $length) {
                break;
            }
        }

        // Remove punctuations and extra spaces
        return preg_replace(['/[^\w\s]/', '/\s+/'], ['', ' '], $text);
    }

    public static function generateMarkovChain(int $length, array $matrix): string
    {
        $startingToken = self::pickRandomStartingToken($matrix);
        $text = ucfirst($startingToken);
        // We can count number of words at the beginning this way
        // as long as we are sure we don't have punctuation marks there
        // which is checked for starting token anyways.
        $numberOfWords = count(explode(' ', $startingToken));
        // $lookForward = $numberOfWords;

        while (true) {
            // We can encounter this situation for the last token
            if (!array_key_exists($startingToken, $matrix)) {
                $startingToken = self::pickRandomStartingToken($matrix);
            }
            $newToken = self::weightedChoice($matrix[$startingToken]);
            $text .= ' ' . $newToken;
            if (self::isWord($newToken)) {
                $numberOfWords++;
            }
            $shiftedToken = implode(' ', array_slice(explode(' ', $startingToken), 1));
            $startingToken = $shiftedToken !== '' ? $shiftedToken . ' ' . $newToken : $newToken;

            if ($numberOfWords >= $length && preg_match('/\.+|!|\?/', $newToken) === 1) {
                break;
            }
        }

        return self::cleanupText($text);
    }

    private static function pickRandomStartingToken(array $matrix): string
    {
        // Pick random starting token
        $punctuationsRegex = '/'
            . rtrim(array_reduce(self::PUNCTUATIONS, static fn ($carry, $item) => $carry . preg_quote($item, '/') . '|', ''), '|')
            . '/';
        do {
            $startingToken = array_rand($matrix);
        } while (preg_match($punctuationsRegex, $startingToken));

        return $startingToken;
    }

    private static function cleanupText(string $text): string
    {
        return preg_replace([
            '/ \./',
            '/ ,/',
            '/ !/',
            '/ \?/',
            '/¡ /',
            '/¿ /',
            '/\( /',
            '/ \)/',
            '/ :/',
            '/" ([-\'\xC2\xAD\p{L}\p{N}]+) "/u',
        ], [
            '.',
            ',',
            '!',
            '?',
            '¡',
            '¿',
            '(',
            ')',
            ':',
            '"${1}"',
        ], $text);
    }

    /**
     * @param array $weightedValues
     * @return string
     * @throws Exception
     */
    private static function weightedChoice(array $weightedValues): string
    {
        $randomValue = '';
        $rand = random_int(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                $randomValue = $key;
                break;
            }
        }

        return (string) $randomValue;
    }

    #[ArrayShape(['tokens' => 'array', 'distinctTokens' => 'array'])]
    public static function tokenize(string $corpus): array
    {
        $corpus = self::normalizeCorpus($corpus);

        // Tokenize
        $tokens = array_filter(explode(' ', $corpus), static fn(string $token) => $token !== '');
        $distinctTokens = array_unique($tokens);

        return [
            'tokens' => $tokens,
            'distinctTokens' => $distinctTokens,
        ];
    }

    private static function normalizeCorpus(string $corpus): string
    {
        // Split punctuations from words
        $corpus = trim(preg_replace(
            array_map(static fn (string $punctuation) => '/' . preg_quote($punctuation, '/') . '/', self::PUNCTUATIONS),
            ' ${0} ',
            $corpus
        ));

        // Normalize corpus
        return preg_replace([
            '/\xa0/u', // Non-breaking space
            '/“/',
            '/”/',
            '/\s+/',

        ], [
            ' ',
            ' " ',
            ' " ',
            ' ',
        ], $corpus);
    }

    private static function isWord(string $token): bool
    {
        return preg_match('/^[-\'\xC2\xAD\p{L}\p{N}]+$/u', $token) === 1;
    }
}
