<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Facets\Fetcher;

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Facets\CriterionList;
use ICanBoogie\Facets\Fetcher;
use ICanBoogie\Facets\FetcherTrait;
use ICanBoogie\Facets\ModelBindings;
use ICanBoogie\Facets\QueryString;
use ICanBoogie\Facets\RecordCollection;

/**
 * Fetch records from a model.
 *
 * @property-read Model $model The model from which record are fetched.
 * @property-read CriterionList $criterion_list List of criterion.
 * @property-read array $modifiers An array of key/value used to filter/order/qualify the records.
 * @property-read Query $initial_query The initial query, before it is altered by the criteria,
 * conditions, order or limit.
 * @property-read Query $query The query used to fetch the records.
 * @property-read QueryString $query_string A {@link QueryString} instance resolved from the `q`
 * modifier.
 * @property-read array $conditions An array of conditions used to filter the fetched records.
 * @property-read string $order The order in which records are fetched, as defined by the `order`
 * modifier.
 * @property-read int $count The number of records matching the query before the offset and limit
 * is applied.
 * @property-read int $limit The maximum number of records that can be fetched, as defined by the
 * `limit` modifier.
 *
 * @template TValue of \ICanBoogie\ActiveRecord
 * @implements Fetcher<TValue>
 */
class BasicFetcher implements Fetcher
{
    /**
     * @uses get_model
     * @uses get_modifiers
     * @uses get_query_string
     * @uses get_order
     * @uses get_offset
     */
    use AccessorTrait;

    /**
     * @use FetcherTrait<TValue>
     */
    use FetcherTrait;

    /**
     * The model from witch records are fetched.
     */
    private Model $model;

    protected function get_model(): Model
    {
        return $this->model;
    }

    /**
     * Fetch modifiers.
     *
     * @var array<string, mixed>
     */
    private array $modifiers;

    /**
     * @return array<string, mixed>
     */
    protected function get_modifiers(): array
    {
        return $this->modifiers;
    }

    /**
     * Options.
     *
     * @var array<string, mixed>
     */
    private array $options;

    private Query $initial_query;

    protected function get_initial_query(): Query
    {
        return $this->initial_query ??= $this->create_initial_query();
    }

    /**
     * The query used to fetch the records.
     */
    private Query $query;

    protected function get_query(): Query
    {
        return $this->query;
    }

    /**
     * Query string resolved from the modifiers.
     */
    private QueryString $query_string;

    protected function get_query_string(): QueryString
    {
        return $this->query_string;
    }

    /**
     * Conditions resolved from the modifiers.
     *
     * @var array<string, mixed>
     */
    private array $conditions = [];

    /**
     * @return array<string, mixed>
     */
    protected function get_conditions(): array
    {
        return $this->conditions;
    }

    /**
     * Order of the records, as found in the modifiers.
     */
    private ?string $order;

    protected function get_order(): ?string
    {
        return $this->order;
    }

    /**
     * Limit of the number of records to fetch, as found in the modifiers.
     */
    private int|null $limit;

    protected function get_limit(): ?int
    {
        return $this->limit;
    }

    private ?int $offset;

    protected function get_offset(): ?int
    {
        return $this->offset;
    }

    protected function get_page(): int
    {
        $limit = $this->limit;

        if (!$limit) {
            return 0;
        }

        return (int) ($this->offset / $this->limit);
    }

    /**
     * Number of records matching the query, before they are limited.
     */
    private int $count;

    protected function get_count(): int
    {
        return $this->count;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(Model $model, array $options = [])
    {
        $this->model = $model;
        $this->options = $options;
        /** @var Model|ModelBindings $model */
        $this->criterion_list = $this->alter_criterion_list($model->criterion_list); // @phpstan-ignore-line
    }

    /**
     * Clones the {@link initial_query}, {@link query}, and {@link query_string} properties.
     */
    public function __clone()
    {
        $this->initial_query = clone $this->initial_query;
        $this->query = clone $this->query;
        $this->query_string = clone $this->query_string;
    }

    /**
     * Fetch records according to the specified modifiers.
     *
     * The method updates the following properties:
     *
     * - {@link $conditions}
     * - {@link $count}
     * - {@link $initial_query}
     * - {@link $limit}
     * - {@link $modifiers}
     * - {@link $offset}
     * - {@link $order}
     * - {@link $query_string}
     *
     * @param array<string, mixed> $modifiers
     *
     * @return RecordCollection<TValue>
     */
    public function __invoke(array $modifiers): RecordCollection
    {
        $this->modifiers = $modifiers;

        [ $conditions, $properties ] = $this->parse_modifiers($modifiers);

        $this->conditions = $conditions;

        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }

        $query = clone $this->get_initial_query();
        $query = $this->alter_query($query);
        $query = $this->alter_query_with_conditions($query, $conditions);
        $this->count = $this->count_records($query);

        if ($this->order) {
            $query = $this->alter_query_with_order($query, $this->order);
        }

        $query = $this->alter_query_with_limit($query, $this->offset ?? 0, $this->limit);

        $this->query = $query;

        $records = $this->fetch_records($query);
        $this->alter_records($records);

        return new RecordCollection($records, clone $this);
    }

    /**
     * Create the initial query.
     */
    protected function create_initial_query(): Query
    {
        return new Query($this->model);
    }

    /**
     * Parse modifiers to extract conditions, and qualifiers.
     *
     * @param array<string, mixed> $modifiers
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function parse_modifiers(array $modifiers): array
    {
        $modifiers += [

            'order' => null,
            'limit' => null,
            'page' => null,
            'q' => null

        ];

        $query_string = $this->parse_query_string($modifiers['q']);

        $conditions = [];
        $this->alter_conditions($conditions, $modifiers + $query_string->conditions);

        $limit = $modifiers['limit'];
        $page = $modifiers['page'];

        return [ $conditions, [

            'order' => $modifiers['order'],
            'limit' => $limit,
            'offset' => $limit && $page ? $page * $limit : null,
            'query_string' => $query_string

        ] ];
    }
}
