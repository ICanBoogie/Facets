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

/**
 * Representation of a query string.
 *
 * @property-read QueryStringWord[] $matched Query string words for which a match was found.
 * @property-read QueryStringWord[] $not_matched Query string words for which no match was found.
 * @property-read QueryStringWord[] $matches Unique matches.
 */
class QueryString implements \IteratorAggregate
{
	use AccessorTrait;

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
				if ($matches) return null;

				continue;
			}

			$matches[] = $word;
			$i++;

			if ($i == $count) break;
		}

		if ($i != $count)
		{
			return null;
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
			return null;
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

		if ($i + 1 == count($this->words))
		{
			return null;
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
	 * Returns the query string words that do not have a match.
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

	/**
	 * Returns the query string words that have a match.
	 *
	 * @return QueryStringWord[]
	 */
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
