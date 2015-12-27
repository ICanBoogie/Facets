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
 */
class AlterEvent extends Event
{
	const TYPE = 'alter';

	/**
	 * @var RecordCollection
	 */
	private $instance;

	protected function get_instance()
	{
		return $this->instance;
	}

	protected function set_instance(RecordCollection $instance)
	{
		$this->instance = $instance;
	}

	/**
	 * @param RecordCollection $target
	 */
	public function __construct(RecordCollection &$target)
	{
		$this->instance = &$target;

		parent::__construct($target, self::TYPE);
	}
}
