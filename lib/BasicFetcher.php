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
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Query;

/**
 * Fetch records from a model.
 *
 * @property-read Model $model The model from which record are fetched.
 * @property-read CriterionList $criterion_list List of criterion.
 * @property-read array $modifiers An array of key/value used to filter/order/qualify the records.
 * @property-read Query $initial_query The initial query, before it is altered by the criteria,
 * conditions, order or limit.
 * @property-read Query $query The query used to fetch the records.
 * @property-read QueryString $query_string A {@link QueryString} instance resolved from the `q`
 * modifier.
 * @property-read array $conditions An array of conditions used to filter the fetched records.
 * @property-read string $order The order in which records are fetched, as defined by the `order`
 * modifier.
 * @property-read int $count The number of records matching the query before the offset and limit
 * is applied.
 * @property-read int $limit The maximum number of records that can be fetched, as defined by the
 * `limit` modifier.
 */
class BasicFetcher implements Fetcher
{
	use AccessorTrait;
	use FetcherTrait;

	/**
	 * The model from witch records are fetched.
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Return the {@link $model} property.
	 *
	 * @return Model
	 */
	protected function get_model()
	{
		return $this->model;
	}

	/**
	 * Fetch modifiers.
	 *
	 * @var array
	 */
	protected $modifiers;

	/**
	 * Return the {@link $modifiers} property.
	 *
	 * @return array
	 */
	protected function get_modifiers()
	{
		return $this->modifiers;
	}

	/**
	 * Options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Initial query.
	 *
	 * @var Query
	 */
	private $initial_query;

	/**
	 * Return the {@link $initial_query} property.
	 *
	 * @return Query
	 */
	protected function get_initial_query()
	{
		if (empty($this->initial_query))
		{
			$this->initial_query = $this->create_initial_query();
		}

		return $this->initial_query;
	}

	/**
	 * The query used to fetch the records.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Return the query used to fetch the records.
	 *
	 * @return Query
	 */
	protected function get_query()
	{
		return $this->query;
	}

	/**
	 * Query string resolved from the modifiers.
	 *
	 * @var QueryString
	 */
	protected $query_string;

	/**
	 * Return the {@link $query_string} property.
	 *
	 * @return QueryString
	 */
	protected function get_query_string()
	{
		return $this->query_string;
	}

	/**
	 * Conditions resolved from the modifiers.
	 *
	 * @var array
	 */
	protected $conditions = [];

	/**
	 * Return the {@link $conditions} property.
	 *
	 * @return array
	 */
	protected function get_conditions()
	{
		return $this->conditions;
	}

	/**
	 * Order of the records, as found in the modifiers.
	 *
	 * @var string|null
	 */
	protected $order;

	/**
	 * Return the {@link $order} property.
	 *
	 * @return string|null
	 */
	protected function get_order()
	{
		return $this->order;
	}

	/**
	 * Limit of the number of records to fetch, as found in the modifiers.
	 *
	 * @var string|null
	 */
	protected $limit;

	/**
	 * Return the {@link $limit} property.
	 *
	 * @return int|null
	 */
	protected function get_limit()
	{
		return $this->limit;
	}

	protected $offset;

	protected function get_offset()
	{
		return $this->offset;
	}

	protected function get_page()
	{
		$limit = $this->limit;

		if (!$limit)
		{
			return 0;
		}

		return (int) ($this->offset / $this->limit);
	}

	/**
	 * Number of records matching the query, before they are limited.
	 *
	 * @var int
	 */
	protected $count;

	/**
	 * Return the {@link $count} property.
	 *
	 * @return int
	 */
	protected function get_count()
	{
		return $this->count;
	}

	/**
	 * Initializes the {@link $model}, {@link $options} and {@link $criterion_list} properties.
	 *
	 * @param Model|ModelBindings $model
	 * @param array $options
	 */
	public function __construct(Model $model, array $options = [])
	{
		$this->model = $model;
		$this->options = $options;
		$this->criterion_list = $this->alter_criterion_list($model->criterion_list);
	}

	/**
	 * Clones the {@link initial_query}, {@link query}, and {@link query_string} properties.
	 */
	public function __clone()
	{
		$this->initial_query = clone $this->initial_query;
		$this->query = clone $this->query;
		$this->query_string = clone $this->query_string;
	}

	/**
	 * Fetch records according to the specified modifiers.
	 *
	 * The method updates the following properties:
	 *
	 * - {@link $conditions}
	 * - {@link $count}
	 * - {@link $initial_query}
	 * - {@link $limit}
	 * - {@link $modifiers}
	 * - {@link $offset}
	 * - {@link $order}
	 * - {@link $query_string}
	 *
	 * @param array $modifiers
	 *
	 * @return array The records matching the query.
	 */
	public function __invoke(array $modifiers)
	{
		$this->modifiers = $modifiers;

		list($conditions, $properties) = $this->parse_modifiers($modifiers);

		$this->conditions = $conditions;

		foreach ($properties as $property => $value)
		{
			$this->$property = $value;
		}

		#

		$query = clone $this->get_initial_query();

		$query = $this->alter_query($query);
		$query = $this->alter_query_with_conditions($query, $conditions);
		$this->count = $this->count_records($query);

		$query = $this->alter_query_with_order($query, $this->order);
		$query = $this->alter_query_with_limit($query, $this->offset, $this->limit);

		$this->query = $query;

		$records = $this->fetch_records($query);

		$this->alter_records($records);

		return new RecordCollection($records, clone $this);
	}

	/**
	 * Create the initial query.
	 *
	 * @return Query
	 */
	protected function create_initial_query()
	{
		return new Query($this->model);
	}

	/**
	 * Parse modifiers to extract conditions, and qualifiers.
	 *
	 * @param array $modifiers
	 *
	 * @return array
	 */
	protected function parse_modifiers(array $modifiers)
	{
		$modifiers += [

			'order' => null,
			'limit' => null,
			'page' => null,
			'q' => null

		];

		$query_string = $this->parse_query_string($modifiers['q']);

		$conditions = [];
		$this->alter_conditions($conditions, $modifiers + $query_string->conditions);

		$limit = $modifiers['limit'];
		$page = $modifiers['page'];

		return [ $conditions, [

			'order' => $modifiers['order'],
			'limit' => $limit,
			'offset' => $limit && $page ? $page * $limit : null,
			'query_string' => $query_string

		] ];
	}
}
