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
use PHPUnit\Framework\TestCase;

class AlterEventTest extends TestCase
{
    /**
     * @var RecordCollection
     */
    private $collection;

    protected function setUp(): void
    {
        $this->collection = $this
            ->getMockBuilder(RecordCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        EventCollectionProvider::define(function () {

            return new EventCollection();
        });
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
