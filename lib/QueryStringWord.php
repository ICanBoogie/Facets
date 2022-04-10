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

use ICanBoogie\Accessor\AccessorTrait;

use function ICanBoogie\normalize;

/**
 * Representation of a query string word.
 *
 * @property-read string $normalized The normalized word.
 * @property-read QueryStringWord|null $previous Previous query string word, if any.
 * @property-read QueryStringWord|null $next Next query string word, if any.
 */
class QueryStringWord
{
    /**
     * @uses get_normalized
     * @uses get_previous
     * @uses get_next
     */
    use AccessorTrait;

    /**
     * @var array<string, mixed>
     */
    public array $match = [];

    private string $word;

    /**
     * Normalized {@link $word}.
     *
     * @var string
     */
    private string $normalized;

    protected function get_normalized(): string
    {
        return $this->normalized;
    }

    private QueryString $q;

    protected function get_previous(): ?QueryStringWord
    {
        return $this->q->before($this);
    }

    protected function get_next(): ?QueryStringWord
    {
        return $this->q->after($this);
    }

    public function __construct(string $word, QueryString $q)
    {
        $this->word = $word;
        $this->normalized = normalize($word);
        $this->q = $q;
    }

    /**
     * Returns the query string word.
     */
    public function __toString(): string
    {
        return $this->word;
    }
}
