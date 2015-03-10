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

/**
 * Traits for an active record fetcher class.
 *
 * @property-read CriterionList $criterion_list List of criterion.
 */
trait FetcherTrait
{
	/**
	 * Criterion list.
	 *
	 * @var CriterionList
	 */
	protected $criterion_list;

	protected function get_criterion_list()
	{
		return $this->criterion_list;
	}

	public function alter_criterion_list(CriterionList $criterion_list)
	{
		return $criterion_list;
	}

	public function parse_query_string($q)
	{
		return $this->criterion_list->parse_query_string($q);
	}

	/**
	 * Return the conditions altered by the {@link CriterionList} instance.
	 *
	 * @param array $conditions
	 * @param array $modifiers
	 *
	 * @return array The altered criterion list.
	 */
	public function alter_conditions(array &$conditions, array $modifiers)
	{
		$this->criterion_list->alter_conditions($conditions, $modifiers);

		return $conditions;
	}

	public function alter_query(Query $query)
	{
		$this->criterion_list->alter_query($query);

		return $query;
	}

	public function alter_query_with_conditions(Query $query, array $conditions)
	{
		$this->criterion_list->alter_query_with_conditions($query, $conditions);

		return $query;
	}

	public function alter_query_with_order(Query $query, $criterion_id, $order_direction=1)
	{
		$this->criterion_list->alter_query_with_order($query, $criterion_id, $order_direction);

		return $query;
	}

	public function count_records(Query $query)
	{
		return $query->count;
	}

	public function alter_query_with_limit(Query $query, $offset, $limit)
	{
		return $query->limit($offset, $limit);
	}

	public function fetch_records(Query $query)
	{
		return $query->all;
	}

	public function alter_records(array &$records)
	{
		$this->criterion_list->alter_records($records);
	}
}
