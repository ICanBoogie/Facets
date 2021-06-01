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

use PHPUnit\Framework\TestCase;

class CriterionListTest extends TestCase
{
	public function test_get_undefined()
	{
		$this->expectException(CriterionNotDefined::class);
		$l = new CriterionList;
		$l['undefined'];
	}
}
