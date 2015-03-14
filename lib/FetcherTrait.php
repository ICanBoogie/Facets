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

	/**
	 * Returns the criterion list.
	 *
	 * @return CriterionList
	 */
	protected function get_criterion_list()
	{
		return $this->criterion_list;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_criterion_list(CriterionList $criterion_list)
	{
		return $criterion_list;
	}

	/**
	 * @inheritdoc
	 */
	public function parse_query_string($q)
	{
		return $this->criterion_list->parse_query_string($q);
	}

	/**
	 * @inheritdoc
	 */
	public function alter_conditions(array &$conditions, array $modifiers)
	{
		$this->criterion_list->alter_conditions($conditions, $modifiers);

		return $conditions;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_query(Query $query)
	{
		$this->criterion_list->alter_query($query);

		return $query;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_query_with_conditions(Query $query, array $conditions)
	{
		$this->criterion_list->alter_query_with_conditions($query, $conditions);

		return $query;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_query_with_order(Query $query, $criterion_id, $order_direction=1)
	{
		$this->criterion_list->alter_query_with_order($query, $criterion_id, $order_direction);

		return $query;
	}

	/**
	 * @inheritdoc
	 */
	public function count_records(Query $query)
	{
		return $query->count;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_query_with_limit(Query $query, $offset, $limit)
	{
		return $query->limit($offset, $limit);
	}

	/**
	 * @inheritdoc
	 */
	public function fetch_records(Query $query)
	{
		return $query->all;
	}

	/**
	 * @inheritdoc
	 */
	public function alter_records(array &$records)
	{
		$this->criterion_list->alter_records($records);
	}
}
