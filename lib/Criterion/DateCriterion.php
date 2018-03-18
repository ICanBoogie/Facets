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

/**
 * A boolean criterion.
 */
class DateCriterion extends BasicCriterion
{
	public function alter_query_with_value(Query $query, $value): Query
	{
		if (!$value)
		{
			return $query;
		}

		$field = $this->id;

		list($year, $month, $day) = explode('-', $value) + [ 0, 0, 0 ];

		if ($day)
		{
			$query->and("DATE(`$field`) = ?", "$year-$month-$day");
		}
		else if ($month)
		{
			$query->and("YEAR(`$field`) = ? AND MONTH(`$field`) = ?", (int) $year, (int) $month);
		}
		else if ($year)
		{
			$query->and("YEAR(`$field`) = ?", (int) $year);
		}

		return $query;
	}
}
