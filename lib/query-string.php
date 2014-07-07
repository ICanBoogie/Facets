<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\PropertyNotDefined;

/**
 * @property-read QueryStringWord[] $matched Query string words for which a match was found.
 * @property-read QueryStringWord[] $not_matched Query string words for which no match was found.
 */
class QueryString implements \IteratorAggregate
{
	use \ICanBoogie\PrototypeTrait;

	static private function parse_phrase($phrase)
	{
		$words = explode(' ', $phrase);
		$words = array_map('trim', $words);
		$words = array_filter($words);
		$words = array_unique($words);

		return $words;
	}

	protected $query_string;
	protected $words;

	public function __construct($query_string)
	{
		$this->query_string = (string) $query_string;

		$words = self::parse_phrase($query_string);

		foreach ($words as &$word)
		{
			$word = new QueryStringWord($word, $this);
		}

		$this->words = $words;
	}

	public function __toString()
	{
		return $this->query_string;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->words);
	}

	/**
	 * Search the phrase in the query string.
	 *
	 * @param string $phrase
	 *
	 * @return QueryStringWord[]|null
	 */
	public function search($phrase)
	{
		$words = self::parse_phrase($phrase);
		$normalized_words = array_map('ICanBoogie\normalize', $words);

		$i = 0;
		$count = count($normalized_words);
		$matches = [];

		foreach ($this->words as $word)
		{
			$normalized_word = $normalized_words[$i];

			if ($word->normalized != $normalized_word)
			{
				if ($matches) return;

				continue;
			}

			$matches[] = $word;
			$i++;

			if ($i == $count) break;
		}

		if ($i != $count)
		{
			return;
		}

		return $matches;
	}

	/**
	 * Returns the word before the specified query string word, if any.
	 *
	 * @param QueryStringWord $word
	 *
	 * @return QueryStringWord|null
	 */
	public function before(QueryStringWord $word)
	{
		$i = array_search($word, $this->words);

		if ($i == 0)
		{
			return;
		}

		return $this->words[$i - 1];
	}

	/**
	 * Returns the word after the specified query string word, if any.
	 *
	 * @param QueryStringWord $word
	 *
	 * @return QueryStringWord|null
	 */
	public function after(QueryStringWord $word)
	{
		$i = array_search($word, $this->words);

		if ($i + 2 == count($this->words))
		{
			return;
		}

		return $this->words[$i + 1];
	}

	/**
	 * Returns the query string words that have a match.
	 *
	 * @return QueryStringWord[]
	 */
	protected function get_matched()
	{
		$rc = [];

		foreach ($this->words as $word)
		{
			if (!$word->match)
			{
				continue;
			}

			$rc[] = $word;
		}

		return $rc;
	}

	/**
	 * Returns the query string words that have no match.
	 *
	 * @return QueryStringWord[]
	 */
	protected function get_not_matched()
	{
		$rc = [];

		foreach ($this->words as $word)
		{
			if ($word->match)
			{
				continue;
			}

			$rc[] = $word;
		}

		return $rc;
	}

	protected function get_matches()
	{
		$matches = [];

		foreach ($this->words as $word)
		{
			foreach ($word->match as $criterion_id => $match)
			{
				$matches[$criterion_id][] = $match;
			}
		}

		$matches = array_map('array_unique', $matches);

		return $matches;
	}
}

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