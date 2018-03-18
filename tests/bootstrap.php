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

require __DIR__ . '/../vendor/autoload.php';

function prepare_error_test(\PHPUnit\Framework\TestCase $test)
{
	if (version_compare(PHP_VERSION, 7, '>='))
	{
		$test->markTestSkipped("Fatal error in PHP7");
	}

	$test->setExpectedException('PHPUnit_Framework_Error');
}
