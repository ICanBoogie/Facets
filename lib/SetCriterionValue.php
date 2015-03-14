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

use ICanBoogie\ToArray;

/**
 * Representation of a set of values, suitable for the SQL `IN()` function.
 *
 * A set of values is created by concatenating values with the pipe sign ("|") e.g. "1|2|3".
 */
class SetCriterionValue implements ToArray, \Countable
{
	const SEPARATOR = '|';

	/**
	 * Instantiate a {@link SetCriterionValue} instance from a value.
	 *
	 * @param mixed $value
	 *
	 * @return SetCriterionValue|null
	 */
	static public function from($value)
	{
		if (!$value)
		{
			return null;
		}

		if (is_array($value))
		{
			if (current($value) !== 'on')
			{
				return new static($value);
			}

			$set = array_keys($value);
		}
		else
		{
			$value = trim($value);

			if ($value === SetCriterionValue::SEPARATOR || strpos($value, SetCriterionValue::SEPARATOR) === false)
			{
				return null;
			}

			$set = explode(self::SEPARATOR, $value);
		}

		$set = array_map('trim', $set);
		$set = array_unique($set);
		$set = array_values($set);

		return new static($set);
	}

	protected $set;

	public function __construct(array $set)
	{
		$this->set = $set;
	}

	/**
	 * Formats the set into a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return implode(self::SEPARATOR, $this->set);
	}

	/**
	 * Returns the number of members in the set.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->set);
	}

	/**
	 * Returns the set as an array.
	 *
	 * @return array
	 */
	public function to_array()
	{
		return $this->set;
	}
}
