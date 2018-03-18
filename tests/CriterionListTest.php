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

class CriterionListTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @expectedException \ICanBoogie\Facets\CriterionNotDefined
	 */
	public function test_get_undefined()
	{
		$l = new CriterionList;
		$l['undefined'];
	}
}
