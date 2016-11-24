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

use ICanBoogie\Accessor\AccessorTrait;

use function ICanBoogie\normalize;

/**
 * Representation of a query string word.
 *
 * @property-read string $normalized The normalized word.
 * @property-read QueryStringWord|null $previous Previous query string word, if any.
 * @property-read QueryStringWord|null $next Next query string word, if any.
 */
class QueryStringWord
{
	use AccessorTrait;

	public $match = [];

	/**
	 * @var string
	 */
	protected $word;

	/**
	 * Normalized {@link $word}.
	 *
	 * @var string
	 */
	protected $normalized;

	protected function get_normalized()
	{
		return $this->normalized;
	}

	protected $q;

	/**
	 * @return QueryStringWord|null
	 */
	protected function get_previous()
	{
		return $this->q->before($this);
	}

	/**
	 * @return QueryStringWord|null
	 */
	protected function get_next()
	{
		return $this->q->after($this);
	}

	public function __construct($word, QueryString $q)
	{
		$this->word = $word;
		$this->normalized = normalize($word);
		$this->q = $q;
	}

	/**
	 * Returns the query string word.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->word;
	}
}
