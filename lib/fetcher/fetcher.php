<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

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
 */
class Fetcher implements FetcherInterface
{
	use \ICanBoogie\PrototypeTrait;
	use \ICanBoogie\ActiveRecord\FetcherTrait;

	/**
	 * The model from witch records are fetched.
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Return the {@link $model} property.
	 *
	 * @return \ICanBoogie\ActiveRecord\Model
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
	 * @return \ICanBoogie\ActiveRecord\Query
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
	 * @return \ICanBoogie\ActiveRecord\QueryString
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
	 * Initialiazes the {@link $model}, {@link $options} and {@link $criterion_list} properties.
	 *
	 * @param Model $model
	 * @param array $options
	 */
	public function __construct(Model $model, array $options=[])
	{
		$this->model = $model;
		$this->options = $options;
		$this->criterion_list = $this->alter_criterion_list($model->criterion_list);
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

		return $records;
	}

	/**
	 * Create the initial query.
	 *
	 * @return \ICanBoogie\ActiveRecord\Query
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

		$order = $modifiers['order'];
		$limit = $modifiers['limit'];
		$offset = $limit ? $modifiers['page'] * $limit : null;
		$query_string = $this->parse_query_string($modifiers['q']);

		if ($query_string->matches)
		{
			$modifiers += array_map(function($v) { return implode('|', $v); }, $query_string->matches);
		}

		$conditions = [];

		$this->alter_conditions($conditions, $modifiers);

		return [

			$conditions, [

				'order' => $order,
				'limit' => $limit,
				'offset' => $offset,
				'query_string' => $query_string

			]

		];
	}
}