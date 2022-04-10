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

use ICanBoogie\Facets\CriterionValue\SetCriterionValue;

/**
 * A trait that implements `humanize()` and can be provided with pairs of key/value where key is
 * the machine name and value is the human name.
 */
trait HumanizePairsTrait
{
    abstract protected function get_humanize_pairs();

    public function humanize($value)
    {
        $pairs = $this->get_humanize_pairs();

        if ($value instanceof SetCriterionValue) {
            $humanized = [];

            foreach ($value->to_array() as $value) {
                if (empty($pairs[$value])) {
                    continue;
                }

                $humanized[] = $pairs[$value];
            }

            return $humanized;
        }

        return empty($pairs[$value]) ? null : $pairs[$value];
    }
}
