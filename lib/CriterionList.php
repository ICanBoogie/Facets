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

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ToArray;

/**
 * A list of criteria.
 */
class CriterionList implements \IteratorAggregate, \ArrayAccess, ToArray
{
	/**
	 * @var Criterion[]
	 */
	private $criterion_list = [];

	/**
	 * @param array $criterion_list A list of criteria.
	 */
	public function __construct(array $criterion_list = [])
	{
		foreach ($criterion_list as $criterion_id => &$criterion)
		{
			if (\is_string($criterion))
			{
				$criterion = new $criterion($criterion_id);
			}

			$this[$criterion_id] = $criterion;
		}
	}

	/**
	 * Clones the criteria of the criterion list.
	 */
	public function __clone()
	{
		foreach ($this->criterion_list as $criterion_id => &$criterion)
		{
			$criterion = clone $criterion;
		}
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->criterion_list);
	}

	public function offsetExists($criterion_id)
	{
		return isset($this->criterion_list[$criterion_id]);
	}

	public function offsetGet($criterion_id)
	{
		if (!$this->offsetExists($criterion_id))
		{
			throw new CriterionNotDefined([ $criterion_id, $this ]);
		}

		return $this->criterion_list[$criterion_id];
	}

	public function offsetSet($criterion_id, $criterion)
	{
		$this->criterion_list[$criterion_id] = $criterion;
	}

	public function offsetUnset($criterion_id)
	{
		unset($this->criterion_list[$criterion_id]);
	}

	/**
	 * @inheritdoc
	 */
	public function to_array(): array
	{
		return $this->criterion_list;
	}

	/**
	 * Parses the query string and marks words matched by criteria.
	 *
	 * @param QueryString|string $q
	 *
	 * @return QueryString
	 */
	public function parse_query_string($q): QueryString
	{
		if (!$q instanceof QueryString)
		{
			$q = new QueryString($q);
		}

		foreach ($this->criterion_list as $criterion)
		{
			$criterion->parse_query_string($q);
		}

		return $q;
	}

	/**
	 * Alters the conditions according to the specified modifiers.
	 *
	 * The {@link Criterion::alter_conditions()} method is invoked for each criterion.
	 *
	 * @param array $conditions The conditions to alter.
	 * @param array $modifiers The modifiers.
	 *
	 * @return $this
	 */
	public function alter_conditions(array &$conditions, array $modifiers): self
	{
		foreach ($this->criterion_list as $criterion)
		{
			$criterion->alter_conditions($conditions, $modifiers);
		}

		return $this;
	}

	/**
	 * Alters the query with initial requirements.
	 *
	 * The {@link Criterion::alter_query()} method is invoked for each criterion.
	 *
	 * @param Query $query
	 *
	 * @return $this
	 */
	public function alter_query(Query &$query): self
	{
		foreach ($this->criterion_list as $criterion)
		{
			$query = $criterion->alter_query($query);
		}

		return $this;
	}

	/**
	 * Alters the query with the criteria matching the values.
	 *
	 * The {@link Criterion::alter_query_with_value()} method is invoked for each criterion
	 * matching the a value.
	 *
	 * @param Query $query The query to alter.
	 * @param array $values The criteria values, as returned by the {@link alter_conditions()} method.
	 *
	 * @return $this
	 */
	public function alter_query_with_conditions(Query &$query, array $values): self
	{
		foreach ($this->criterion_list as $criterion)
		{
			if (!\array_key_exists($criterion->id, $values))
			{
				continue;
			}

			$value = $values[$criterion->id];
			$value = $criterion->parse_value($value);

			$query = $criterion->alter_query_with_value($query, $value);
		}

		return $this;
	}

	/**
	 * Alters the query with a criterion and an order direction.
	 *
	 * The {@link Criterion::alter_query_with_order()} method is invoked on the criterion
	 * matching the `$criterion_id` parameter.
	 *
	 * @param Query $query
	 * @param string $criterion_id Criterion identifier. If prefixed with the minus sign "-"
	 * `$order_direction` is overrode with `-1`.
	 * @param int $order_direction The direction of the order: 1 ascending, -1 descending.
	 * Default: 1.
	 *
	 * @return $this
	 */
	public function alter_query_with_order(Query &$query, string $criterion_id, int $order_direction = 1): self
	{
		if ($criterion_id{0} == '-')
		{
			$order_direction = -1;
			$criterion_id = \substr($criterion_id, 1);
		}

		if (empty($this->criterion_list[$criterion_id]))
		{
			return $this;
		}

		$query = $this->criterion_list[$criterion_id]
			->alter_query_with_order($query, $order_direction);

		return $this;
	}

	/**
	 * Alters the records with the criteria.
	 *
	 * The {@link Criterion::alter_records()} method is invoked for each criterion.
	 *
	 * @param array $records
	 *
	 * @return $this
	 */
	public function alter_records(array &$records): self
	{
		foreach ($this->criterion_list as $criterion)
		{
			$criterion->alter_records($records);
		}

		return $this;
	}

	/**
	 * Returns human readable values.
	 *
	 * @param array $conditions
	 *
	 * @return string[]
	 */
	public function humanize(array $conditions): array
	{
		$humanized = [];

		foreach ($this->criterion_list as $criterion_id => $criterion)
		{
			if (!isset($conditions[$criterion_id]) || $conditions[$criterion_id] === '')
			{
				continue;
			}

			$value = $criterion->parse_value($conditions[$criterion_id]);
			$humanized[$criterion_id] = $criterion->format_humanized_value($criterion->humanize($value));
		}

		return \array_filter($humanized);
	}
}
