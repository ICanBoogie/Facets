<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Facets;

use ICanBoogie\ActiveRecord\CriterionList;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Core;

class Hooks
{
	/**
	 * Synthesize the `activerecord.facets` config from `activerecord` fragments.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	static public function synthesize_config(array $fragments)
	{
		$facets = [];

		foreach ($fragments as $fragment)
		{
			if (empty($fragment['facets']))
			{
				continue;
			}

			foreach ($fragment['facets'] as $model_id => $criteria)
			{
				if (empty($facets[$model_id]))
				{
					$facets[$model_id] = $criteria;

					continue;
				}

				$facets[$model_id] = array_merge($facets[$model_id], $criteria);
			}
		}

		return $facets;
	}

	/**
	 * Return the criteria associated with the specified model.
	 *
	 * The criteria include the criteria of the parent models.
	 *
	 * @param Model $model
	 *
	 * @return array
	 */
	static public function criteria_from(Model $model)
	{
		$criteria_list = [];
		$facets = Core::get()->configs['activerecord.facets'];

		$m = $model;

		while ($m)
		{
			$id = $m->id;

			if (!empty($facets[$id]))
			{
				$criteria_list[] = $facets[$id];
			}

			$m = $m->parent_model;
		}

		return call_user_func_array('array_merge', array_reverse($criteria_list));
	}

	/**
	 * Return the {@link CriterionList} instance associated with the specified model.
	 *
	 * @param Model $model
	 *
	 * @return CriterionList
	 */
	static public function criterion_list_from(Model $model)
	{
		static $instances = [];

		$model_id = $model->id;

		if (isset($instances[$model_id]))
		{
			return $instances[$model_id];
		}

		$criteria = $model->criteria;
		$instances[$model_id] = $criterion_list = new CriterionList($criteria);

		return $criterion_list;
	}
}