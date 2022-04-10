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

use ICanBoogie\ActiveRecord\ConnectionCollection;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\ModelCollection;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\Schema;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Facets\Criterion\BooleanCriterion;
use PHPUnit\Framework\TestCase;

class BooleanCriterionTest extends TestCase
{
    private static Model $model;

    public static function setupBeforeClass(): void
    {
        $connections = new ConnectionCollection([

            'primary' => 'sqlite::memory:'

        ]);

        $models = new ModelCollection($connections, [

            'one' => [

                Model::NAME => 'example',
                Model::SCHEMA => new Schema([

                    'id' => SchemaColumn::serial(),
                    'is_online' => SchemaColumn::boolean(),

                ])
            ]
        ]);

        self::$model = $models['one'];
    }

    /**
     * @dataProvider provide_boolean_value
     */
    public function test_parse_value($expected, $value)
    {
        $criterion = new BooleanCriterion('online');

        $this->assertSame($expected, $criterion->parse_value($value));
    }

    /**
     * @dataProvider provide_boolean_value
     */
    public function test_alter_query_with_value($expected, $value)
    {
        $query = new Query(self::$model);
        $criterion = new BooleanCriterion('online', [ 'column_name' => 'is_online' ]);

        $value = $criterion->parse_value($value);
        $criterion->alter_query_with_value($query, $value);

        $this->assertSame([ "(`is_online` = ?)" ], $query->conditions);
        $this->assertSame([ $expected ], $query->conditions_args);
    }

    public function provide_boolean_value()
    {
        return [

            [ false, '' ],
            [ false, 'abba' ],
            [ false, '0' ],
            [ false, 'no' ],
            [ false, 'false' ],
            [ false, 'off' ],
            [ false, false ],

            [ true, '1' ],
            [ true, 'yes' ],
            [ true, 'true' ],
            [ true, 'on' ],
            [ true, true ]

        ];
    }
}
