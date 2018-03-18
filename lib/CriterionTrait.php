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
use ICanBoogie\Facets\CriterionValue\IntervalCriterionValue;
use ICanBoogie\Facets\CriterionValue\SetCriterionValue;

/**
 * Trait for a generic criterion.
 */
trait CriterionTrait
{
	/**
	 * The identifier of the criterion.
	 *
	 * @var string
	 */
	private $id;

	protected function get_id(): string
	{
		return $this->id;
	}

	/**
	 * The column name of the criterion, as in a SQL table.
	 *
	 * @var string
	 */
	private $column_name;

	protected function get_column_name(): string
	{
		return $this->column_name ?: $this->id;
	}

	/**
	 * Parses the query string and marks words matched by the criterion.
	 *
	 * @param QueryString $q
	 */
	public function parse_query_string(QueryString $q): void
	{

	}

	/**
	 * Parses the criterion value using {@link CriterionValue::from()}.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function parse_value($value)
	{
		return CriterionValue::from($value);
	}

	/**
	 * Unset the condition if the modifier is `null` or an empty string.
	 *
	 * @inheritdoc
	 */
	public function alter_conditions(array &$conditions, array $modifiers): void
	{
		if (!isset($modifiers[$this->id]) || $modifiers[$this->id] === '')
		{
			unset($conditions[$this->id]);

			return;
		}

		$conditions[$this->id] = $modifiers[$this->id];
	}

	/**
	 * @inheritdoc
	 */
	public function alter_query(Query $query): Query
	{
		return $query;
	}

	/**
	 * Alters the query according to the specified value.
	 *
	 * The method handles {@link IntervalCriterionValue} and {@link SetCriterionValue} instances as well
	 * as plain values, for which a simple `{$this->id} = {$value}` is done.
	 *
	 * Subclasses might want to override the method according to the kind of value they provide.
	 *
	 * @param Query $query
	 * @param mixed $value The criterion value. Special care is taken if the param is an
	 * instance of {@link IntervalCriterionValue} or {@link SetCriterionValue}.
	 *
	 * @return Query
	 */
	public function alter_query_with_value(Query $query, $value): Query
	{
		if ($value instanceof IntervalCriterionValue)
		{
			if ($value->min === null)
			{
				return $query->and("`$this->column_name` <= ?", $value->max);
			}

			if ($value->max === null)
			{
				return $query->and("`$this->column_name` >= ?", $value->min);
			}

			return $query->and("`$this->column_name` BETWEEN ? AND ?", $value->min, $value->max);
		}

		if ($value instanceof SetCriterionValue)
		{
			$value = $value->to_array();
		}

		return $query->and([ $this->column_name => $value ]);
	}

	/**
	 * Alters the query with an order.
	 *
	 * The {@link $column_name} property is used.
	 *
	 * @param Query $query
	 * @param int $order_direction "DESC" if inferior to 0, "ASC" otherwise.
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction): Query
	{
		return $query->order("`$this->column_name` " . ($order_direction < 0 ? 'DESC' : 'ASC'));
	}

	/**
	 * @inheritdoc
	 */
	public function alter_records(array &$records): void
	{

	}

	/**
	 * Returns a human readable value.
	 *
	 * @param mixed $value
	 *
	 * @return string|IntervalCriterionValue
	 */
	public function humanize($value)
	{
		if ($value instanceof IntervalCriterionValue)
		{
			return "$value->min – $value->max";
		}

		return $value;
	}

	/**
	 * Formats a humanized value, or array of values, into a string.
	 *
	 * @param mixed $humanized_value
	 *
	 * @return string
	 */
	public function format_humanized_value($humanized_value)
	{
		if (\is_array($humanized_value))
		{
			return \implode(', ', $humanized_value);
		}

		return $humanized_value;
	}
}
