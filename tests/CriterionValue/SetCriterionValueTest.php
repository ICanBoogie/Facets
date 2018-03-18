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

class SetCriterionValueTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @dataProvider provide_test_from
	 */
	public function test_from($s, $expected)
	{
		$v = SetCriterionValue::from($s);

		$this->assertInstanceOf(SetCriterionValue::class, $v);
		$this->assertSame($expected, $v->to_array());
	}

	public function provide_test_from()
	{
		return [

			[ [ null ] ,   [ null ] ],
			[ [ "1" ] ,   [ '1' ] ],
			[ [ "one" ] ,   [ 'one' ] ],
			[ [ '1' => 'on' ], [ '1' ] ],
			[ [ 'one' => 'on' ], [ 'one' ] ],
			[ "1|2|3" ,   [ '1', '2', '3' ] ],
			[ "one|two|three" ,   [ 'one', 'two', 'three' ] ],
			[ [ '1' => 'on', '2' => 'on', '3' => 'on' ], [ '1', '2', '3' ] ],
			[ [ 'one' => 'on', 'two' => 'on', 'three' => 'on' ], [ 'one', 'two', 'three' ] ]

		];
	}

	/**
	 * @dataProvider provide_test_from_faulty
	 */
	public function test_from_faulty($from)
	{
		$v = SetCriterionValue::from($from);

		$this->assertNull($v);
	}

	public function provide_test_from_faulty()
	{
		return [

			[ null ],
			[ '' ],
			[ SetCriterionValue::SEPARATOR ],
			[ " " . SetCriterionValue::SEPARATOR ],
			[ SetCriterionValue::SEPARATOR . " " ],
			[ " " . SetCriterionValue::SEPARATOR . " " ],
			[ "1" ,   [ '1' ] ],
			[ "one" ,   [ 'one' ] ]

		];
	}
}
