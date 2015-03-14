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

/**
 * Representation of a generic criterion value.
 *
 * @package ICanBoogie\Facets
 */
class CriterionValue
{
	/**
	 * Creates an instance from the specified criterion value.
	 *
	 * Only instances of {@link SetCriterionValue} and {@link IntervalCriterionValue} are currently
	 * supported. The criterion value is returned as is if it cannot be represented by either one
	 * of these classes.
	 *
	 * Note: {@link IntervalCriterionValue} instances are replaced with simpler values whenever
	 * possible. For instance `1..1` or `[ 'min' => 1, 'max' => 1 ]` are both replaced by `1`.
	 *
	 * @param mixed $value
	 *
	 * @return SetCriterionValue|IntervalCriterionValue|mixed
	 */
	static public function from($value)
	{
		if ((!$value && $value !== 0 && $value !== '0')
		|| $value === SetCriterionValue::SEPARATOR
		|| $value === IntervalCriterionValue::SEPARATOR)
		{
			return null;
		}

		#

		$instance = IntervalCriterionValue::from($value);

		if ($instance instanceof IntervalCriterionValue)
		{
			if ($instance->min == $instance->max)
			{
				return $instance->min;
			}

			return $instance;
		}

		#

		$instance = SetCriterionValue::from($value);

		if ($instance instanceof SetCriterionValue)
		{
			if ($instance->count() !== 1)
			{
				return $instance;
			}

			$value = (string) $instance;
		}

		#

		return trim($value);
	}

	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}
