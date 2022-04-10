<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Facets;

use ArrayIterator;
use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\Facets\CriterionValue\SetCriterionValue;
use IteratorAggregate;

use function array_filter;
use function array_map;
use function array_search;
use function array_unique;
use function count;
use function explode;
use function implode;
use function reset;

/**
 * Representation of a query string.
 *
 * @property-read QueryStringWord[] $matched Query string words for which a match was found.
 * @property-read QueryStringWord[] $not_matched Query string words for which no match was found.
 * @property-read array<string, mixed> $matches Unique matches.
 * @property-read array<string, mixed> $conditions
 *   An array of conditions suitable for {@link Criterion::alter_query_with_conditions}.
 * @property-read string $remains What remains of the query string after removing matched words.
 *
 * @implements IteratorAggregate<int, QueryStringWord>
 */
class QueryString implements IteratorAggregate
{
    /**
     * @uses get_matches
     * @uses get_matched
     * @uses get_not_matched
     * @uses get_conditions
     * @uses get_remains
     */
    use AccessorTrait;

    /**
     * @return array<int, string>
     */
    private static function parse_phrase(string $phrase): array
    {
        $words = explode(' ', $phrase);
        $words = array_map('trim', $words);
        $words = array_filter($words);

        return array_values(array_unique($words));
    }

    private string $query_string;

    /**
     * @var array<int, QueryStringWord>
     */
    private array $words;

    public function __construct(string $query_string)
    {
        $this->query_string = $query_string;
        $this->words = array_map(
            fn($word) => new QueryStringWord($word, $this),
            self::parse_phrase($query_string)
        );
    }

    public function __toString(): string
    {
        return $this->query_string;
    }

    /**
     * @return ArrayIterator<int, QueryStringWord>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->words);
    }

    /**
     * Search the phrase in the query string.
     *
     * @return QueryStringWord[]|null
     */
    public function search(string $phrase): ?iterable
    {
        $words = self::parse_phrase($phrase);
        $normalized_words = array_map('ICanBoogie\normalize', $words);

        $i = 0;
        $count = count($normalized_words);
        $matches = [];

        foreach ($this->words as $word) {
            $normalized_word = $normalized_words[$i];

            if ($word->normalized != $normalized_word) {
                if ($matches) {
                    return null;
                }

                continue;
            }

            $matches[] = $word;
            $i++;

            if ($i == $count) {
                break;
            }
        }

        if ($i != $count) {
            return null;
        }

        return $matches;
    }

    /**
     * Returns the word before the specified query string word, if any.
     */
    public function before(QueryStringWord $word): ?QueryStringWord
    {
        $i = array_search($word, $this->words);

        if ($i === 0) {
            return null;
        }

        return $this->words[$i - 1];
    }

    /**
     * Returns the word after the specified query string word, if any.
     */
    public function after(QueryStringWord $word): ?QueryStringWord
    {
        $i = array_search($word, $this->words);

        if ($i + 1 === count($this->words)) {
            return null;
        }

        return $this->words[$i + 1];
    }

    /**
     * Returns the query string words that have a match.
     *
     * @return QueryStringWord[]
     */
    protected function get_matched(): array
    {
        $rc = [];

        foreach ($this->words as $word) {
            if (!$word->match) {
                continue;
            }

            $rc[] = $word;
        }

        return $rc;
    }

    /**
     * Returns the query string words that do not have a match.
     *
     * @return QueryStringWord[]
     */
    protected function get_not_matched(): array
    {
        $rc = [];

        foreach ($this->words as $word) {
            if ($word->match) {
                continue;
            }

            $rc[] = $word;
        }

        return $rc;
    }

    /**
     * Returns criterion values per criterion identifier.
     *
     * @return array<string, array<int, string>>
     */
    protected function get_matches(): array
    {
        $matches = [];

        foreach ($this->matched as $word) {
            foreach ($word->match as $criterion_id => $match) {
                $matches[$criterion_id][] = $match;
            }
        }

        return array_map('array_unique', $matches);
    }

    /**
     * Returns an array of facet conditions.
     *
     * @return array<string, mixed>
     */
    protected function get_conditions(): array
    {
        return array_map(function ($v) {

            if (count($v) === 1) {
                return reset($v);
            }

            return new SetCriterionValue($v);
        }, $this->matches);
    }

    /**
     * Returns what remains of the query string after removing matched words.
     */
    protected function get_remains(): string
    {
        return implode(' ', $this->not_matched);
    }
}
