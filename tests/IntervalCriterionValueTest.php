<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

class IntervalCriterionValueTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_from
	 */
	public function test_from($s, $expected)
	{
		$v = IntervalCriterionValue::from($s);

		$this->assertSame($expected[0], $v->min);
		$this->assertSame($expected[1], $v->max);
		$this->assertSame($expected, $v->to_array());
	}

	public function provide_test_from()
	{
		$s = IntervalCriterionValue::SEPARATOR;

		return [

			[ "123{$s}" ,   [ '123', null ] ],
			[ "{$s}456" ,   [ null, '456' ] ],
			[ "123{$s}456", [ '123', '456' ] ],
			[ "123{$s}123", [ '123', '123' ] ]

		];
	}

	/**
	 * @dataProvider provide_test_from_array
	 */
	public function test_from_array($a, $expected)
	{
		$v = IntervalCriterionValue::from($a);

		$this->assertInstanceOf('ICanBoogie\ActiveRecord\IntervalCriterionValue', $v);
		$this->assertSame($expected[0], $v->min);
		$this->assertSame($expected[1], $v->max);
		$this->assertSame($expected, $v->to_array());
	}

	public function provide_test_from_array()
	{
		return [

			[ [ 'min' => null, 'max' => null ], [ null, null ] ],
			[ [ 'min' => 1, 'max' => 1 ], [ 1, 1 ] ],
			[ [ 'min' => 123, 'max' => null ], [ 123, null ] ],
			[ [ 'min' => '123', 'max' => null ], [ '123', null ] ],
			[ [ 'min' => 123, 'max' => '' ], [ 123, null ] ],
			[ [ 'min' => '123', 'max' => '' ], [ '123', null ] ],
			[ [ 'min' => null, 'max' => 456 ], [ null, 456 ] ],
			[ [ 'min' => null, 'max' => '456' ], [ null, '456' ] ],
			[ [ 'min' => '', 'max' => 456 ], [ null, 456 ] ],
			[ [ 'min' => '', 'max' => '456' ], [ null, '456' ] ]

		];
	}

	/**
	 * @dataProvider provide_test_from_faulty
	 */
	public function test_from_faulty($from)
	{
		$v = IntervalCriterionValue::from($from);

		$this->assertNull($v);
	}

	public function provide_test_from_faulty()
	{
		return [

			[ null ],
			[ IntervalCriterionValue::SEPARATOR ],
			[ " " . IntervalCriterionValue::SEPARATOR ],
			[ IntervalCriterionValue::SEPARATOR . " " ],
			[ " " . IntervalCriterionValue::SEPARATOR . " " ],
			[ 123 ],
			[ [ 123, 456 ] ],
			[ [ 'min' => 123, 456 ] ],
			[ [ 123, 'max' => 456 ] ],
			[ [ 123, 456] ]

		];
	}

	/**
	 * @dataProvider provide_test_to_string
	 */
	public function test_to_string($from, $expected)
	{
		$v = IntervalCriterionValue::from($from);

		$this->assertSame($expected, (string) $v);
	}

	public function provide_test_to_string()
	{
		return [

			[ '123..456', '123..456' ],
			[ ' 123..456', '123..456' ],
			[ '123..456 ', '123..456' ],
			[ ' 123..456 ', '123..456' ],

			[ '123..', '123..' ],
			[ ' 123.. ', '123..' ],

			[ '..456', '..456' ],
			[ ' ..456 ', '..456' ],

			[ '123..123', '123' ],
			[ [ 'min' => 123, 'max' => 123 ], '123' ],
			[ [ 'min' => null, 'max' => null ], '' ],

		];
	}
}