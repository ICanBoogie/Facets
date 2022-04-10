<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Facets\Criterion;

use ICanBoogie\ActiveRecord\Query;

use function is_string;

/**
 * A boolean criterion.
 *
 * @template-extends BasicCriterion<string>
 */
class DateCriterion extends BasicCriterion
{
    public function alter_query_with_value(Query $query, mixed $value): Query
    {
        if (!$value) {
            return $query;
        }

        assert(is_string($value));

        $field = $this->id;

        [ $year, $month, $day ] = explode('-', $value) + [ 0, 0, 0 ];

        if ($day) {
            $query->and("DATE(`$field`) = ?", "$year-$month-$day");
        } elseif ($month) {
            $query->and("YEAR(`$field`) = ? AND MONTH(`$field`) = ?", (int) $year, (int) $month);
        } elseif ($year) {
            $query->and("YEAR(`$field`) = ?", (int) $year);
        }

        return $query;
    }
}
