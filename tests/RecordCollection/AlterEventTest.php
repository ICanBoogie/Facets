<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Facets\RecordCollection;

use ICanBoogie\EventCollection;
use ICanBoogie\EventCollectionProvider;
use ICanBoogie\Facets;
use ICanBoogie\Facets\RecordCollection;

class AlterEventTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var RecordCollection
	 */
	private $collection;

	public function setUp()
	{
		$this->collection = $this
			->getMockBuilder(RecordCollection::class)
			->disableOriginalConstructor()
			->getMock();

		EventCollectionProvider::using(function() {

			return new EventCollection;

		});
	}

	public function test_error_on_invalid_collection_type()
	{
		Facets\prepare_error_test($this);

		$collection = new \StdClass;

		new AlterEvent($collection);
	}

	public function test_error_on_setting_invalid_response_type()
	{
		Facets\prepare_error_test($this);

		$event = new AlterEvent($this->collection);
		$event->instance = new \StdClass;
	}

	public function test_should_not_accept_null()
	{
		Facets\prepare_error_test($this);

		#
		$event = new AlterEvent($this->collection);
		$event->instance = null;
	}

	public function test_response()
	{
		$collection = $this->collection;
		$collection_replacement = $this
			->getMockBuilder(RecordCollection::class)
			->disableOriginalConstructor()
			->getMock();

		$event = new AlterEvent($collection);
		$this->assertInstanceOf(RecordCollection::class, $event->instance);
		$this->assertSame($collection, $event->instance);
		$event->instance = $collection_replacement;
		$this->assertSame($collection_replacement, $event->instance);
		$this->assertSame($collection_replacement, $collection);
	}
}
