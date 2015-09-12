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
use ICanBoogie\ActiveRecord;

/**
 * A collection of records fetched by a {@link Fetcher} instance.
 *
 * @property-read Fetcher $fetcher
 * @property-read array $conditions The conditions used to fetch the records.
 * @property-read int $limit The maximum number of records.
 * @property-read int $page The current page.
 * @property-read int $total_count The number of records matching the query, without range
 * limitation.
 * @property-read ActiveRecord\Query $initial_query
 * @property-read ActiveRecord\Query $query
 * @property-read ActiveRecord $one The first record in the collection.
 */
class RecordCollection implements \IteratorAggregate, \Countable
{
	use AccessorTrait;

	/**
	 * Properties forwarded to the {@link Fetcher} instance.
	 *
	 * @var array
	 */
	static private $forwarded_properties = [ 'conditions', 'initial_query', 'limit', 'page', 'query' ];

	/**
	 * @var ActiveRecord[]
	 */
	private $records;

	/**
	 * @var Fetcher
	 */
	private $fetcher;

	protected function get_fetcher()
	{
		return $this->fetcher;
	}

	/**
	 * Returns the first record in the collection.
	 *
	 * @return ActiveRecord
	 */
	protected function get_one()
	{
		return reset($this->records);
	}

	/**
	 * Returns the number of records matching the query, without range limitation.
	 *
	 * @return int
	 */
	protected function get_total_count()
	{
		return $this->fetcher->count;
	}

	public function __construct(array $records, Fetcher $fetcher)
	{
		$this->records = $records;
		$this->fetcher = $fetcher;
	}

	public function __get($property)
	{
		if (in_array($property, self::$forwarded_properties))
		{
			return $this->fetcher->$property;
		}

		return $this->accessor_get($property);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->records);
	}

	/**
	 * @inheritdoc
	 */
	public function count()
	{
		return count($this->records);
	}
}