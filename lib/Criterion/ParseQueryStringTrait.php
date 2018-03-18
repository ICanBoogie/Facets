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
use ICanBoogie\Facets\QueryStringWord;

/**
 * Use this trait to parse the query string against an array of normalized matchables.
 *
 * @property string $id
 */
trait ParseQueryStringTrait
{
	/**
	 * Matches the {@link QueryString} against an array of normalized matchables.
	 *
	 * @param QueryString $q
	 */
	public function parse_query_string(QueryString $q): void
	{
		$matchables = $this->provide_query_string_matchables();

		/* @var $word QueryStringWord */

		foreach ($q->not_matched as $word)
		{
			$value = array_search($word->normalized, $matchables);

			if ($value === false)
			{
				continue;
			}

			$word->match = [ $this->id => $value ];
		}
	}

	/**
	 * @return array An array of value/normalized_match pairs.
	 */
	abstract protected function provide_query_string_matchables(): array;
}
