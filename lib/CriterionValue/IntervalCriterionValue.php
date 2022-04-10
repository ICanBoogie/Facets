<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Facets\CriterionValue;

use ICanBoogie\ToArray;

use function array_key_exists;
use function array_map;
use function count;
use function explode;
use function is_array;
use function trim;

/**
 * Representation of an interval, suitable for the SQL `BETWEEN` operator.
 *
 * An interval is created by separating two values with two dots ("..") e.g. "2000..2014".
 */
final class IntervalCriterionValue implements ToArray
{
    public const SEPARATOR = '..';

    /**
     * Instantiate a {@link IntervalCriterionValue} instance from a value.
     */
    public static function from(mixed $value): ?self
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            if (!array_key_exists('min', $value) || !array_key_exists('max', $value)) {
                return null;
            }

            $min = $value['min'];
            $max = $value['max'];
        } else {
            $value = trim($value);

            if ($value === self::SEPARATOR || !str_contains($value, self::SEPARATOR)) {
                return null;
            }

            $interval = explode(self::SEPARATOR, $value);

            if (count($interval) != 2) {
                return null;
            }

            [ $min, $max ] = array_map('trim', $interval);
        }

        if ($min === '') {
            $min = null;
        }
        if ($max === '') {
            $max = null;
        }

        return new self($min, $max);
    }

    public int|string|null $min;
    public int|string|null $max;

    public function __construct(int|string|null $min, int|string|null $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function __toString(): string
    {
        if (!$this->min && !$this->max) {
            return '';
        }

        if ($this->min == $this->max) {
            return (string) $this->min;
        }

        return $this->min . self::SEPARATOR . $this->max;
    }

    /**
     * @return array{ 0: int|string|null, 1: int|string|null}
     *     An array made of the {@link $min} and {@link max} values.
     */
    public function to_array(): array
    {
        return [ $this->min, $this->max ];
    }
}
