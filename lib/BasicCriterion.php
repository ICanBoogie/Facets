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
 * Representation of a generic criterion.
 *
 * @property-read string $id The identifier of the criterion.
 * @property-read string $column_name The column name of the criterion.
 */
class BasicCriterion implements Criterion
{
	use AccessorTrait;
	use CriterionTrait;

	/**
	 * Initializes the {@link $id} and {@link $column_name} properties.
	 *
	 * @param string $id
	 * @param array $options
	 */
	public function __construct($id, array $options = [])
	{
		$this->id = $id;
		$this->column_name = empty($options['column_name']) ? $id : $options['column_name'];
	}
}
