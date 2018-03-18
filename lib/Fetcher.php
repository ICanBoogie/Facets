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
 * Interface for an active record fetcher that supports external conditions.
 */
interface Fetcher
{
	/**
	 * Alter the {@link CriterionList} instance usually provided during construct.
	 *
	 * @param CriterionList $criterion_list
	 *
	 * @return CriterionList
	 */
	public function alter_criterion_list(CriterionList $criterion_list): CriterionList;

	/**
	 * Alter the conditions with the specified modifiers.
	 *
	 * A {@link CriterionList} instance is usually used to alter the conditions.
	 *
	 * @param array $conditions The conditions to alter, usually initialized
	 * @param array $modifiers
	 */
	public function alter_conditions(array &$conditions, array $modifiers): void;

	/**
	 * Parse the query string.
	 *
	 * The query string is usually specified by the `q` condition.
	 *
	 * The conditions extracted from the query string are merged in the conditions.
	 *
	 * A {@link CriterionList} instance is usually used to parse the query string.
	 *
	 * @param QueryString|string $q
	 *
	 * @return QueryString
	 */
	public function parse_query_string($q): QueryString;

	/**
	 * Alter the initial query.
	 *
	 * A {@link CriterionList} instance is usually used to alter the initial query.
	 *
	 * @param Query $query
	 *
	 * @return Query The altered initial query.
	 */
	public function alter_query(Query $query): Query;

	/**
	 * Alter the query with conditions.
	 *
	 * A {@link CriterionList} instance is usually used to alter the query with conditions.
	 *
	 * @param Query $query
	 * @param array $conditions
	 *
	 * @return Query The altered query.
	 */
	public function alter_query_with_conditions(Query $query, array $conditions): Query;

	/**
	 * Alter the query with an order.
	 *
	 * A {@link CriterionList} instance is usually used to alter the query with an order.
	 *
	 * @param Query $query
	 * @param string $criterion_id
	 * @param int $order_direction
	 *
	 * @return Query The altered query.
	 */
	public function alter_query_with_order(Query $query, string $criterion_id, int $order_direction = 1): Query;

	/**
	 * Counts the number of records that are matching the query.
	 *
	 * The method is invoked before the query is altered with a limit, thus the number returned
	 * is the total number of records matching the query.
	 *
	 * @param Query $query
	 *
	 * @return int
	 */
	public function count_records(Query $query): int;

	/**
	 * Alter the query with an offset and limit.
	 *
	 * @param Query $query
	 * @param int $offset
	 * @param int|null $limit
	 *
	 * @return Query The altered query.
	 */
	public function alter_query_with_limit(Query $query, int $offset, ?int $limit): Query;

	/**
	 * Fetch the records matching the query.
	 *
	 * @param Query $query
	 *
	 * @return array
	 */
	public function fetch_records(Query $query): array;

	/**
	 * Alter the fetched records.
	 *
	 * A {@link CriterionList} instance is usually used to alter the records.
	 *
	 * @param array $records
	 */
	public function alter_records(array &$records): void;
}
