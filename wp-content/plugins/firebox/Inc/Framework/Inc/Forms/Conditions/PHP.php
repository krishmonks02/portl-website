<?php

defined('ABSPATH') or die;

return [
	[
		'type' => 'Textarea',
		'name' => 'value',
		'label' => 'FPF_PHP_CODE',
		'description' => sprintf(fpframework()->_('FPF_PHP_SELECTION_DESC'), \FPFramework\Base\Functions::getUTMURL('https://www.fireplugins.com/docs/general/extending-general/custom-php-assignments/', '', 'misc', 'custom-php-conditions')),
		'rows' => 10,
		'filter' => 'php',
		'mode' => 'text/x-php'
	],
	[
		'name' => 'note',
		'type' => 'ConditionRuleValueHint'
	]
];