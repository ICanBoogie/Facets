<?php

namespace ICanBoogie\Facets\Criterion;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Query;
use PHPUnit\Framework\TestCase;

class DateCriterionTest extends TestCase
{
	/**
	 * @dataProvider provide_test_alter_query_with_value
	 *
	 * @param mixed $value
	 */
	public function test_alter_query_with_value(Query $query, $value, callable $assert = null)
	{
		$id = uniqid();
		$criterion = new DateCriterion($id);
		$result_query = $criterion->alter_query_with_value($query, $value);
		$this->assertSame($query, $result_query);

		if ($assert)
		{
			$assert($query, $id);
		}
	}

	public function provide_test_alter_query_with_value(): array
	{
		$model = $this->getMockBuilder(Model::class)
			->disableOriginalConstructor()
			->getMock();

		$q1 = new class($model) extends Query {
		};

		$q2 = new class($model) extends Query {
		};

		$date = new \DateTime;
		$year = $date->format('Y');
		$month = $date->format('m');
		$day = $date->format('d');

		return [

			[ clone $q1, null, null ],
			[ clone $q1, "", null ],
			[ clone $q1, false, null ],

			[ clone $q2, "$year-$month-$day", function(Query $query, $id) use ($year, $month, $day) {

				$this->assertEquals([ "(DATE(`$id`) = ?)" ], $query->conditions);
				$this->assertEquals([ "$year-$month-$day" ], $query->conditions_args);

			} ],

			[ clone $q2, "$year-$month", function(Query $query, $id) use ($year, $month, $day) {

				$this->assertEquals([ "(YEAR(`$id`) = ? AND MONTH(`$id`) = ?)" ], $query->conditions);
				$this->assertEquals([ $year, $month ], $query->conditions_args);

			} ],

			[ clone $q2, "$year", function(Query $query, $id) use ($year, $month, $day) {

				$this->assertEquals([ "(YEAR(`$id`) = ?)" ], $query->conditions);
				$this->assertEquals([ $year ], $query->conditions_args);

			} ],

		];
	}
}
