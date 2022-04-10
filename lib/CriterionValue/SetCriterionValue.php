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

use Countable;
use ICanBoogie\ToArray;

use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function count;
use function current;
use function explode;
use function implode;
use function is_array;
use function trim;

/**
 * Representation of a set of values, suitable for the SQL `IN()` function.
 *
 * A set of values is created by concatenating values with the pipe sign ("|") e.g. "1|2|3".
 */
final class SetCriterionValue implements ToArray, Countable
{
    public const SEPARATOR = '|';

    /**
     * Instantiate a {@link SetCriterionValue} instance from a value.
     */
    public static function from(mixed $value): ?self
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            if (current($value) !== 'on') {
                return new self($value);
            }

            $set = array_keys($value);
        } else {
            $value = trim($value);

            if ($value === self::SEPARATOR || !str_contains($value, self::SEPARATOR)) {
                return null;
            }

            $set = explode(self::SEPARATOR, $value);
        }

        $set = array_map('trim', $set);
        $set = array_unique($set);
        $set = array_values($set);

        return new self($set);
    }

    /**
     * @var array<int, mixed>
     */
    private array $set;

    /**
     * @param array<int, mixed> $set
     */
    public function __construct(array $set)
    {
        $this->set = $set;
    }

    /**
     * Formats the set into a string.
     */
    public function __toString(): string
    {
        return implode(self::SEPARATOR, $this->set);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->set);
    }

    /**
     * @inheritdoc
     *
     * @return array<int, mixed>
     */
    public function to_array(): array
    {
        return $this->set;
    }
}
