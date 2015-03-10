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

use ICanBoogie\PropertyNotDefined;

/**
 * Representation of a query string word.
 *
 * @property-read string $normalized The normalized word.
 * @property-read QueryStringWord|null $previous Previous query string word, if any.
 * @property-read QueryStringWord|null $next Next query string word, if any.
 */
class QueryStringWord
{
	protected $word;
	protected $normalized;
	public $match = [];
	protected $q;

	public function __construct($word, QueryString $q)
	{
		$this->word = $word;
		$this->normalized = \ICanboogie\normalize($word);
		$this->q = $q;
	}

	public function __toString()
	{
		return $this->word;
	}

	public function __get($property)
	{
		switch ($property)
		{
			case 'normalized':

				return $this->normalized;

			case 'previous':

				return $this->q->before($this);

			case 'next':

				return $this->q->after($this);
		}

		throw new PropertyNotDefined([ $property, $this]);
	}
}
