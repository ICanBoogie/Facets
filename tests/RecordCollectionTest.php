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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Facets\Fetcher\BasicFetcher;
use PHPUnit\Framework\TestCase;

class RecordCollectionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fetcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $initial_query;

    /**
     * @var array
     */
    private $records;

    /**
     * @var RecordCollection
     */
    private $collection;

    protected function setUp(): void
    {
        $criterion_list = $this
            ->getMockBuilder(CriterionList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model = $this
            ->getMockBuilder(ActiveRecord\Model::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'get_criterion_list' ])
            ->getMock();
        $model
            ->expects($this->any())
            ->method('get_criterion_list')
            ->willReturn($criterion_list);

        $initial_query = $this
            ->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fetcher = $this
            ->getMockBuilder(BasicFetcher::class)
            ->setConstructorArgs([ $model ])
            ->setMethods([ 'get_conditions', 'get_count', 'get_initial_request', 'get_limit', 'get_page', 'get_query', 'create_initial_query' ])
            ->getMock();
        $fetcher
            ->expects($this->any())
            ->method('create_initial_query')
            ->willReturn($initial_query);

        $records = [

            $this->getMockBuilder(ActiveRecord::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ActiveRecord::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ActiveRecord::class)->disableOriginalConstructor()->getMock()

        ];

        /* @var $fetcher BasicFetcher */

        $this->fetcher = $fetcher;
        $this->initial_query = $initial_query;
        $this->records = $records;
        $this->collection = new RecordCollection($records, $fetcher);
    }

    public function test_get_fetcher()
    {
        $this->assertSame($this->fetcher, $this->collection->fetcher);
    }

    public function test_get_one()
    {
        $this->assertSame(reset($this->records), $this->collection->one);
    }

    public function test_get_total_count()
    {
        $expected = rand(1, 30);

        $this->fetcher
            ->expects($this->once())
            ->method('get_count')
            ->willReturn($expected);

        $this->assertSame($expected, $this->collection->total_count);
    }

    public function test_get_count()
    {
        $expected = rand(1, 30);

        $this->fetcher
            ->expects($this->never())
            ->method('get_count')
            ->willReturn($expected);

        $this->assertSame(count($this->records), count($this->collection));
    }

    public function test_get_limit()
    {
        $expected = rand(1, 30);

        $this->fetcher
            ->expects($this->once())
            ->method('get_limit')
            ->willReturn($expected);

        $this->assertSame($expected, $this->collection->limit);
    }

    public function test_get_conditions()
    {
        $expected = [ 'order' => uniqid(), 'user' => uniqid() ];

        $this->fetcher
            ->expects($this->once())
            ->method('get_conditions')
            ->willReturn($expected);

        $this->assertSame($expected, $this->collection->conditions);
    }

    public function test_get_initial_query()
    {
        $expected = $this->initial_query;
        /* @var $fetcher BasicFetcher */
        $fetcher = $this->fetcher;

        $this->assertSame($expected, $fetcher->initial_query);
        $this->assertSame($expected, $this->collection->initial_query);
    }

    public function test_get_query()
    {
        $expected = $this
            ->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetcher
            ->expects($this->once())
            ->method('get_query')
            ->willReturn($expected);

        $this->assertSame($expected, $this->collection->query);
    }

    public function test_iterator()
    {
        foreach ($this->collection as $k => $v) {
            $this->assertSame($this->records[$k], $v);
        }
    }
}
