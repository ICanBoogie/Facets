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

use ICanBoogie\Event;
use ICanBoogie\Facets\RecordCollection;

/**
 * Event class for the `ICanBoogie\Facets\RecordCollection::alter` event.
 *
 * Event hooks may use this event to alter records fetched by a {@link BasicFetcher} instance.
 *
 * @property RecordCollection $instance
 *
 * @template TValue of \ICanBoogie\ActiveRecord
 */
class AlterEvent extends Event
{
    public const TYPE = 'alter';

    /**
     * @var RecordCollection<TValue>
     */
    private RecordCollection $instance;

    /**
     * @return RecordCollection<TValue>
     */
    protected function get_instance(): RecordCollection
    {
        return $this->instance;
    }

    /**
     * @param RecordCollection<TValue> $instance
     */
    protected function set_instance(RecordCollection $instance): void
    {
        $this->instance = $instance;
    }

    /**
     * @param RecordCollection<TValue> $target
     */
    public function __construct(RecordCollection &$target)
    {
        $this->instance = &$target;

        parent::__construct($target, self::TYPE);
    }
}
