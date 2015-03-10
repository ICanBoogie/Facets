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
 * An interface common to Criteria.
 */
interface CriterionInterface
{
	public function parse_query_string(QueryString $q);

	/**
	 * Parse a criterion value.
	 *
	 * @param mixed $value
	 */
	public function parse_value($value);

	/**
	 * Alter the conditions according to the specified modifiers.
	 *
	 * @param array $conditions The conditions to alter.
	 * @param array $modifiers The modifiers.
	 */
	public function alter_conditions(array &$conditions, array $modifiers);

	/**
	 * Alters the initial query.
	 *
	 * @param Query $query
	 *
	 * @return Query $query The altered query.
	 */
	public function alter_query(Query $query);

	/**
	 * Alters the query according to the value specified.
	 *
	 * Note: The method is only invoked if a value key matches the criterion identifier.
	 *
	 * @param Query $query
	 * @param mixed $value
	 *
	 * @return Query
	 */
	public function alter_query_with_value(Query $query, $value);

	/**
	 * Alters the ORDER clause of the query according to the column identifier and the order
	 * direction.
	 *
	 * Note: The method is only invoked if the ordering column matches the criterion identifier.
	 *
	 * @param Query $query
	 * @param int $order_direction
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction);

	/**
	 * Alters the records.
	 *
	 * @param array $records
	 *
	 * @return array[]ActiveRecord
	 */
	public function alter_records(array &$records);
}
