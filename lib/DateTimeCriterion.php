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
 * A boolean criterion.
 */
class DateTimeCriterion extends BasicCriterion
{
	public function alter_query_with_value(Query $query, $value)
	{
		if ($value)
		{
			$field = $this->id;

			list($year, $month, $day) = explode('-', $value) + [ 0, 0, 0 ];

			if ($year)
			{
				$query->and("YEAR(`$field`) = ?", (int) $year);
			}

			if ($month)
			{
				$query->and("MONTH(`$field`) = ?", (int) $month);
			}

			if ($day)
			{
				$query->and("DAY(`$field`) = ?", (int) $day);
			}

			echo $query;
		}

		return $query;
	}
}
