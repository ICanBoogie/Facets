<?php

namespace ICanBoogie\ActiveRecord\Facets;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'prototypes' => [

		'ICanBoogie\ActiveRecord\Model::get_criteria' => $hooks . 'criteria_from',
		'ICanBoogie\ActiveRecord\Model::get_criterion_list' => $hooks . 'criterion_list_from'

	]

];