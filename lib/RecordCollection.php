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
use Countable;
use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ActiveRecord;
use ICanBoogie\Facets\Fetcher\BasicFetcher;
use ICanBoogie\ToArray;
use IteratorAggregate;

use function count;
use function in_array;
use function reset;

/**
 * A collection of records fetched by a {@link BasicFetcher} instance.
 *
 * @property-read Fetcher<TValue> $fetcher
 * @property-read array $conditions The conditions used to fetch the records.
 * @property-read int $limit The maximum number of records.
 * @property-read int $page The current page.
 * @property-read int $total_count The number of records matching the query, without range
 * limitation.
 * @property-read ActiveRecord\Query $initial_query
 * @property-read ActiveRecord\Query $query
 * @property-read ActiveRecord $one The first record in the collection.
 *
 * @template TValue of ActiveRecord
 * @implements IteratorAggregate<int, TValue>
 */
class RecordCollection implements IteratorAggregate, Countable, ToArray
{
    /**
     * @uses get_fetcher
     * @uses get_one
     * @uses get_total_count
     */
    use AccessorTrait;

    /**
     * Properties forwarded to the {@link BasicFetcher} instance.
     *
     * @var array<int, string>
     */
    private static array $forwarded_properties = [ 'conditions', 'initial_query', 'limit', 'page', 'query' ];

    /**
     * @var array<int, TValue>
     */
    private array $records;

    /**
     * @var Fetcher<TValue>
     */
    private Fetcher $fetcher;

    /**
     * @return Fetcher<TValue>
     */
    protected function get_fetcher(): Fetcher
    {
        return $this->fetcher;
    }

    /**
     * Returns the first record in the collection.
     *
     * @return TValue|null
     */
    protected function get_one(): ?ActiveRecord
    {
        return reset($this->records) ?: null;
    }

    /**
     * Returns the number of records matching the query, without range limitation.
     */
    protected function get_total_count(): int
    {
        return $this->fetcher->count;
    }

    /**
     * @param array<int, TValue> $records
     * @param Fetcher<TValue> $fetcher
     */
    public function __construct(array $records, Fetcher $fetcher)
    {
        $this->records = $records;
        $this->fetcher = $fetcher;
    }

    public function __get(string $property): mixed
    {
        if (in_array($property, self::$forwarded_properties)) {
            return $this->fetcher->$property;
        }

        return $this->accessor_get($property);
    }

    /**
     * @return ArrayIterator<int, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->records);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->records);
    }

    /**
     * @inheritdoc
     *
     * @return array<int, TValue>
     */
    public function to_array(): array
    {
        return $this->records;
    }
}
