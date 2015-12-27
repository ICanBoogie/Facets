<?php

namespace ICanBoogie\Facets\Criterion;

use ICanBoogie\Facets\QueryString;

class BooleanCriterionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_parse_value
	 *
	 * @param mixed $value
	 * @param bool $expected_value
	 */
	public function test_parse_value($value, $expected_value)
	{
		$this->assertSame($expected_value, (new BooleanCriterion(uniqid()))->parse_value($value));
	}

	public function provide_test_parse_value()
	{
		return [

			[ true, true ],
			[ 1, true ],
			[ '1', true ],
			[ 'yes', true ],
			[ false, false ],
			[ null, false ],
			[ '', false ],
			[ 'a', false ],
			[ 'no', false ],
			[ '0', false ],
			[ 0, false ],

		];
	}

	public function test_parse_query_string()
	{
		$id = uniqid();
		$id2 = uniqid();
		$id3 = uniqid();

		$criterion = new BooleanCriterion($id);
		$q = new QueryString("$id $id2 $id3");
		$criterion->parse_query_string($q);

		$matches = $q->matches;
		$this->assertArrayHasKey($id, $matches);
		$this->assertSame([ true ], $matches[$id]);
	}
}
