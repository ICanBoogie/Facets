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

use ICanBoogie\Facets\QueryString;

/**
 * A boolean criterion.
 */
class BooleanCriterion extends BasicCriterion
{
	/**
	 * @inheritdoc
	 */
	public function parse_value($value)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * @inheritdoc
	 */
	public function parse_query_string(QueryString $q)
	{
		foreach ($q->not_matched as $word)
		{
			if ($word->normalized !== $this->id)
			{
				continue;
			}

			$word->match = [ $this->id => true ];
		}
	}
}
