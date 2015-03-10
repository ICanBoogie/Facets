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

class QueryStringTest extends \PHPUnit_Framework_TestCase
{
	public function test_search()
	{
		$q = new QueryString("The quick brown fox jumps over the lazy dog");

		$words = $q->search('brÔwN Föx');
		$this->assertEquals(2, count($words));
		$this->assertInstanceOf(QueryStringWord::class, $words[0]);
		$this->assertInstanceOf(QueryStringWord::class, $words[1]);
		$this->assertEquals('brown', $words[0]->normalized);
		$this->assertEquals('fox', $words[1]->normalized);

		$words = $q->search('Dôg');
		$this->assertEquals(1, count($words));
		$this->assertInstanceOf(QueryStringWord::class, $words[0]);
		$this->assertEquals('dog', $words[0]->normalized);

		$words = $q->search('dog poop');
		$this->assertEmpty($words);

		$words = $q->search('jumps lazy');
		$this->assertEmpty($words);
	}

	public function test_next_word()
	{
		$q = new QueryString("one two three");
		$words = $q->not_matched;
		$this->assertSame($words[1], $words[0]->next);
		$this->assertSame($words[2], $words[1]->next);
		$this->assertSame(null, $words[2]->next);
	}

	public function test_previous_word()
	{
		$q = new QueryString("one two three");
		$words = $q->not_matched;
		$this->assertSame($words[1], $words[2]->previous);
		$this->assertSame($words[0], $words[1]->previous);
		$this->assertSame(null, $words[0]->previous);
	}
}
