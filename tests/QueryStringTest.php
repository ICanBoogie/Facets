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

class QueryStringTest extends \PHPUnit_Framework_TestCase
{
	public function test_search()
	{
		$q = new QueryString("The quick brown fox jumps over the lazy dog");

		$words = $q->search('brÔwN Föx');
		$this->assertEquals(2, count($words));
		$this->assertInstanceOf('ICanBoogie\ActiveRecord\QueryStringWord', $words[0]);
		$this->assertInstanceOf('ICanBoogie\ActiveRecord\QueryStringWord', $words[1]);
		$this->assertEquals('brown', $words[0]->normalized);
		$this->assertEquals('fox', $words[1]->normalized);

		$words = $q->search('Dôg');
		$this->assertEquals(1, count($words));
		$this->assertInstanceOf('ICanBoogie\ActiveRecord\QueryStringWord', $words[0]);
		$this->assertEquals('dog', $words[0]->normalized);

		$words = $q->search('dog poop');
		$this->assertEmpty($words);

		$words = $q->search('jumps lazy');
		$this->assertEmpty($words);
	}
}