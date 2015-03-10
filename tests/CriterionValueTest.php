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

class CriterionValueTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_from
	 */
	public function test_from($value, $expected)
	{
		$value = CriterionValue::from($value);

		if ($expected === null)
		{
			$this->assertNull($value);
		}
		else
		{
			if (is_object($expected))
			{
				$this->assertInstanceOf(get_class($expected), $value);
			}

			$this->assertEquals($expected, $value);
		}
	}

	public function provide_test_from()
	{
		return [

			[ '', null ],
			[ false, null ],
			[ null, null ],
			[ [], null ],
			[ '0', '0' ],
			[ 0, 0 ],
			[ 'one', 'one' ],
			[ '1', '1' ],

			# set

			[ '|' , null ],
			[ '1|1' , '1' ],
			[ '1|2|3', new SetCriterionValue([ 1, 2, 3 ]) ],
			[ '1|1|1|2|3', new SetCriterionValue([ 1, 2, 3 ]) ],
			[ [ 1, 2, 3 ], new SetCriterionValue([ 1, 2, 3 ]) ],
			[ [ 1 => 'on', 2 => 'on', 3 => 'on' ], new SetCriterionValue([ 1, 2, 3 ]) ],

			# interval

			[ '..', null ],
			[ '1..2', new IntervalCriterionValue(1, 2) ],
			[ '2..1', new IntervalCriterionValue(2, 1) ],
			[ '0..0', 0 ],
			[ '2..2', 2 ],
			[ '..2', new IntervalCriterionValue(null, 2) ],
			[ '1..', new IntervalCriterionValue(1, null) ],
			[ [ 'min' => 1, 'max' => 2 ], new IntervalCriterionValue(1, 2) ],
			[ [ 'min' => 2, 'max' => 1 ], new IntervalCriterionValue(2, 1) ],
			[ [ 'min' => null, 'max' => 2 ], new IntervalCriterionValue(null, 2) ],
			[ [ 'min' => 2, 'max' => null ], new IntervalCriterionValue(2, null) ],

		];
	}
}
