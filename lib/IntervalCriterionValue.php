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
 * Representation of an interval, suitable for the SQL `BETWEEN` operator.
 *
 * An interval is created by separating two values with two dots ("..") e.g. "2000..2014".
 */
class IntervalCriterionValue implements ToArray
{
	const SEPARATOR = '..';

	/**
	 * Instantiate a {@link IntervalCriterionValue} instance from a value.
	 *
	 * @param mixed $value
	 *
	 * @return IntervalCriterionValue|null
	 */
	static public function from($value)
	{
		if (!$value)
		{
			return null;
		}

		if (is_array($value))
		{
			if (!array_key_exists('min', $value) || !array_key_exists('max', $value))
			{
				return null;
			}

			$min = $value['min'];
			$max = $value['max'];
		}
		else
		{
			$value = trim($value);

			if ($value === self::SEPARATOR || strpos($value, self::SEPARATOR) === false)
			{
				return null;
			}

			$interval = explode(self::SEPARATOR, $value);

			if (count($interval) != 2)
			{
				return null;
			}

			list($min, $max) = array_map('trim', $interval);
		}

		if ($min === '') $min = null;
		if ($max === '') $max = null;

		return new static($min, $max);
	}

	public $min;
	public $max;

	public function __construct($min, $max)
	{
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * Formats the interval as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		if (!$this->min && !$this->max)
		{
			return '';
		}

		if ($this->min == $this->max)
		{
			return (string) $this->min;
		}

		return $this->min . self::SEPARATOR . $this->max;
	}

	/**
	 * @return array An array made of the {@link $min} and {@link max} values.
	 */
	public function to_array()
	{
		return [ $this->min, $this->max ];
	}
}
