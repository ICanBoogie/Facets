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
 *
 * @property-read int $count
 *
 * @template TValue of \ICanBoogie\ActiveRecord
 */
interface Fetcher
{
    /**
     * Alter the {@link CriterionList} instance usually provided during construct.
     *
     * @param CriterionList<TValue> $criterion_list
     *
     * @return CriterionList<TValue>
     */
    public function alter_criterion_list(CriterionList $criterion_list): CriterionList;

    /**
     * Alter the conditions with the specified modifiers.
     *
     * A {@link CriterionList} instance is usually used to alter the conditions.
     *
     * @param array<string, mixed> $conditions The conditions to alter, usually initialized
     * @param array<string, mixed> $modifiers
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
     */
    public function parse_query_string(QueryString|string $q): QueryString;

    /**
     * Alter the initial query.
     *
     * A {@link CriterionList} instance is usually used to alter the initial query.
     *
     * @return Query The altered query.
     */
    public function alter_query(Query $query): Query;

    /**
     * Alter the query with conditions.
     *
     * A {@link CriterionList} instance is usually used to alter the query with conditions.
     *
     * @param array<string, mixed> $conditions
     *
     * @return Query The altered query.
     */
    public function alter_query_with_conditions(Query $query, array $conditions): Query;

    /**
     * Alter the query with an order.
     *
     * A {@link CriterionList} instance is usually used to alter the query with an order.
     *
     * @return Query The altered query.
     */
    public function alter_query_with_order(Query $query, string $criterion_id, int $order_direction = 1): Query;

    /**
     * Counts the number of records that are matching the query.
     *
     * The method is invoked before the query is altered with a limit, thus the number returned
     * is the total number of records matching the query.
     */
    public function count_records(Query $query): int;

    /**
     * Alter the query with an offset and limit.
     *
     * @return Query The altered query.
     */
    public function alter_query_with_limit(Query $query, int $offset, ?int $limit): Query;

    /**
     * Fetch the records matching the query.
     *
     * @return array<int, TValue>
     */
    public function fetch_records(Query $query): array;

    /**
     * Alter the fetched records.
     *
     * A {@link CriterionList} instance is usually used to alter the records.
     *
     * @param array<int, TValue> $records
     */
    public function alter_records(array &$records): void;
}
