# Facets

[![Release](https://img.shields.io/packagist/v/icanboogie/facets.svg)](https://github.com/ICanBoogie/Facets/releases)
[![Build Status](https://img.shields.io/github/workflow/status/ICanBoogie/Facets/test)](https://github.com/ICanBoogie/Facets/actions?query=workflow%3Atest)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Facets.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Facets)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Facets.svg)](https://coveralls.io/r/ICanBoogie/Facets)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/facets.svg)](https://packagist.org/packages/icanboogie/facets)

Together with the [icanboogie/activerecord] package, this library makes it easy to implement
[faceted search][]. The library makes it especially easy to parse query strings (bag of words),
use serialized criterion values such as sets (e.g. "1|2|3") or intervals (.e.g. "1990..2010"),
and fetch records matching an array of conditions.





## Fetching records matching conditions

A [BasicFetcher][] instance can be used to fetch records matching a set of conditions.
The _fetcher_ takes care of the various steps required to build the query and fetch the
matching records. These steps can be summarized as follows:

1. Parse the specified modifiers and extract conditions, offset, limit, order and query string.
2. Build the initial query.
3. Invoke criteria to alter the query.
4. Alter the query with the conditions.
5. Count the total number of records that match the query.
6. Alter the query with the order.
7. Alter the query with the offset and limit.
8. Fetch the records matching the query.
9. Invoke criteria to alter the records.
10. Return a [RecordCollection][] instance containing the records.

The following example demonstrates how a [BasicFetcher][] instance can be used to fetch online articles
that are classified in the "music" category, and were published between 2010 and 2014. A maximum of
10 articles can be fetched, and they are ordered starting with the most recent:

```php
<?php

use ICanBoogie\ActiveRecord;
use ICanBoogie\Facets\Fetcher\BasicFetcher;

$model = ActiveRecord\get_model('articles');

$fetcher = new BasicFetcher($model);
$records = $fetcher([

    'year' => "2010..2014",
    'is_online' => true,
    'category' => "music",
    'order' => "-date",
    'limit' => 10

]);
```





### Fetch records using a model

The package adds the `fetch_records()` and `fetch_record()` methods to [Model][] instances,
which allow for records to be fetched directly from the model, without requiring a
[BasicFetcher][] instance to be built.

```php
$records = $model->fetch_records([

    'year' => "2010..2014",
    'is_online' => true,
    'category' => "music",
    'order' => "-date",
    'limit' => 10

]);
```

Note that the [BasicFetcher][] instance created to fetch the records can be obtained using the
second argument of the methods.

```php
$records = $model->fetch_records($conditions, $fetcher);
```






### Properties of interest

Once the records have been returned the following properties might be of interest:

- `query_string`: A [QueryString][] instance resolved from the `q` modifier.
- `conditions`: An array of conditions used to filter the fetched records.
- `order`: The order in which records are fetched, as defined by the `order` modifier.
- `count`: The number of records matching the query before the offset and limit were applied.





### Altering the fetched records

Fetched records are returned as a [RecordCollection][] instance, such an instance can be used to
fire the `alter` event of class [RecordCollection\AlterEvent][]. Event hooks may use this event to
alter the records of the collection, for instance fetching the images associated with a
collection of articles using a single query.

```php
<?php

use ICanBoogie\Facets\RecordCollection;

$records = $fetcher(…);

new RecordCollection\AlterEvent($records);
```





## Fetching records using a `CriterionList` instance

If using a [BasicFetcher][] instance is not enough of a challenge for you, you can use
a [CriterionList][] instead and do all the hard work yourself:

```php
<?php

use ICanBoogie\Facets\CriterionList;
use App\Modules\Vehicles;

$criterion_list = new CriterionList([

    'family'   => Vehicles\Families\FamilyCriterion::class,
    'brand'    => Vehicles\Brands\BrandCriterion::class,
    'category' => Vehicles\Categories\CategoryCriterion::class,
    'color'    => Vehicles\Colors\ColorCriterion::class,
    'energy'   => Vehicles\Energies\EnergyCriterion::class,
    'engine'   => Vehicles\Engines\EngineCriterion::class,
    'doors'    => Vehicles\DoorsCriterion::class,
    'year'     => Vehicles\YearCriterion::class,
    'price'    => Vehicles\PriceCriterion::class

]);

$modifiers = $_GET + [

    'q' => null,     // reserved keyword for query string
    'order' => null  // reserved keyword for records order

];

if ($modifiers['q'])
{
    $q = $criterion_list->parse_query_string($modifiers['q']);

    echo "The following words were matched: " . implode(' ', $q->matched) . '<br />';
    echo "The following words were not matched: " . implode(' ', $q->not_matched) . '<br />';

    // we choose to _OR_ criterion values
    $modifiers += array_map(function($v) { return implode('|', $v); }, $q->matches);
}

#
# Parameters are passed by reference, $values and $query are likely to be modified.
#

$conditions = [];

$criterion_list
->alter_conditions($conditions, $modifiers)
->alter_query($query)
->alter_query_with_conditions($query, $conditions);

if ($modifiers['order'])
{
    $criterion_list->alter_query_with_order($query, $modifiers['order']);
}

$count = $query->count;            // count all the records matching the query
$records = $query->limit(20)->all; // fetch a maximum of 20 records
```





## Criterion values

Criterion values are usually created when a [CriterionList][] instance alters a query with values.
If a value's key matches a criterion identifier, the [parse_value()][] of that criterion is invoked
to retrieve a _criterion value_, which might be the exact same value,
or a [CriterionValue][] instance if the value is complex, for instance an _interval_ or a _set_.

The resulting _criterion value_ is used to alter the query during [alter_query_with_value()].
_Interval values_ result in `BETWEEN ? AND ?`, `=> ?`, and `<= ?` conditions; while _set values_
result in `IN(?)` conditions.





### Interval values

Interval values are represented by [IntervalCriterionValue][] instances. When specified as a string
two dots `..` are used to separate the lower and the upper bound. An interval value can
be created with any of the following statements:

```php
<?php

use ICanBoogie\Facets\CriterionValue\IntervalCriterionValue;

$value = IntervalCriterionValue::from('123..456'); // between 123 and 456
$value = IntervalCriterionValue::from('123..');    // >= 123
$value = IntervalCriterionValue::from('..456');    // <= 456

$value = IntervalCriterionValue::from([ 'min' => '123', 'max' => '456' ]); // between 123 and 456
$value = IntervalCriterionValue::from([ 'min' => '123', 'max' => null ]);  // >= 123
$value = IntervalCriterionValue::from([ 'min' => null, 'max' => '456' ]);  // <= 456

$value = new IntervalCriterionValue(123, 456);  // between 123 and 456
$value = new IntervalCriterionValue(null, 456); // >= 123
$value = new IntervalCriterionValue(123, null); // <= 456
```

[IntervalCriterionValue][] instances can also be used as strings:

```php
<?php

use ICanBoogie\Facets\CriterionValue\IntervalCriterionValue;

echo new IntervalCriterionValue(123, 456);    // "123..456"
echo new IntervalCriterionValue(123, null);   // "123.."
echo new IntervalCriterionValue(null, 456);   // "..456"
echo new IntervalCriterionValue(123, 123);    // "123"
echo new IntervalCriterionValue(null, null);  // ""
```

[IntervalCriterionValue][] instances can be used by criteria to create `BETWEEN ? AND ?`, `>= ?`,
and `<= ?` conditions while they alter the query.





### Set values

Set values are represented by [SetCriterionValue][] instances. When specified as a string the
_pipe_ character "|" is used to separate the values. A set value can be created with any of the
following statements:

```php
<?php

use ICanBoogie\Facets\CriterionValue\SetCriterionValue;

$value = SetCriterionValue::from('1|2');                    // 1 or 2
$value = SetCriterionValue::from([ 1 => 'on', 2 => 'on' ]); // 1 or 2
$value = new SetCriterionValue([ 1, 2 ]);                   // 1 or 2
```

[SetCriterionValue][] instances can also be used as strings:

```php
<?php

use ICanBoogie\Facets\CriterionValue\SetCriterionValue;

echo new SetCriterionValue([ 1, 2, 3 ]); // "1|2|3"
echo new SetCriterionValue([ 1 ]);       // "1"
echo new SetCriterionValue([ ]);         // ""
```

[SetCriterionValue][] instances can be used by criteria to create `IN(?)` conditions while they
alter the query.





## Associating criteria with models

Criteria are associated with models using `activerecord` config fragments and the `facets` key.
The criteria for a model are specified using the model's identifier.
The `activerecord_facets` config is synthesized from fragments. The `criteria` and `criterion_list`
getters are added to the [Model][] class by the package and are used to respectively retrieve the
config criteria and the [CriterionList][] instance associated with a model.

**Note:** This feature currently requires the [ICanBoogie][] framework and the
[icanboogie/bind-facets][] package. A similar feature can be implemented using only the
[icanboogie/prototype][] package. In which case, you only need to define the `criteria` and
`criterion_list` getters for the [Model][] class.

The following example demonstrates how the `nid` and `slug` criteria are associated with the
`nodes` model, and how the `month` and `year` criteria are associated with the `articles` model:

```php
<?php

// config/activerecord.php

return [

    'facets' => [

        'nodes' => [

            'nid' => Icybee\Modules\Nodes\NidCriterion::class,
            'slug' => Icybee\Modules\Nodes\SlugCriterion::class

        ],

        'articles' => [

            'month' => Icybee\Modules\Articles\MonthCriterion::class,
            'year' => Icybee\Modules\Articles\YearCriterion::class

        ]

    ]

];
```

Note that criteria are inherited. In our example, because `articles` extends `nodes` it inherits
its `nid` and `slug` criteria.





### Obtaining the criteria associated with a model

The criteria array can be retrieved from a model using the `criteria` getter that is added by the
package. The getter returns the criteria and inherited criteria as they are defined in the
`activerecord_facets` config.

```php
<?php

use ICanBoogie\ActiveRecord;

$model = ActiveRecord\get_model('articles');

array_keys($model->criteria);
# [ 'nid', 'slug', 'month', 'year' ]
```

The `criteria` and `criterion_list` getters are added to the [Model][] class





### Obtaining the CriterionList instance associated with a model

The [CriterionList] instance associated with a model can be retrieved from a model using the
`criterion_list` getter that is added by the package. The getter returns a [CriterionList]
instance created from the criteria obtained through the `criteria` getter.

```php
<?php

$criterion_list = $model->criterion_list;
```





----------





## Requirements

The package requires PHP 5.5 or later.





## Installation

```bash
composer require icanboogie/facets
```





## Documentation

The package is documented as part of the [ICanBoogie][] framework
[documentation][]. You can generate the documentation for the package and its dependencies with
the `make doc` command. The documentation is generated in the `build/docs` directory.
[ApiGen](http://apigen.org/) is required. The directory can later be cleaned with
the `make clean` command.





## Testing

Run `make test-container` to create and log into the test container, then run `make test` to run the
test suite. Alternatively, run `make test-coverage` to run the test suite with test coverage. Open
`build/coverage/index.html` to see the breakdown of the code coverage.





## License

**icanboogie/facets** is released under the [New BSD License](LICENSE).




[Model]:                       https://icanboogie.org/api/activerecord/3.0/docs/class-ICanBoogie.ActiveRecord.Model.html
[CriterionList]:               https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.CriterionList.html
[CriterionValue]:              https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.CriterionValue.html
[BasicFetcher]:                https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.BasicFetcher.html
[documentation]:               https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.IntervalCriterionValue.html
[IntervalCriterionValue]:      https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.IntervalCriterionValue.html
[QueryString]:                 https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.QueryString.html
[RecordCollection]:            https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.RecordCollection.html
[RecordCollection\AlterEvent]: https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.RecordCollection.AlterEvent.html
[SetCriterionValue]:           https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.SetCriterionValue.html
[alter_query_with_value()]:    https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.CriterionTrait.html#_alter_query_with_value
[parse_value()]:               https://icanboogie.org/api/facets/0.7/class-ICanBoogie.Facets.CriterionTrait.html#_parse_value
[icanboogie/activerecord]:     https://github.com/ICanBoogie/ActiveRecord
[icanboogie/bind-facets]:      https://github.com/ICanBoogie/bind-facets
[icanboogie/prototype]:        https://github.com/ICanBoogie/Prototype
[ICanBoogie]:                  https://icanboogie.org/

[faceted search]: http://en.wikipedia.org/wiki/Faceted_search
