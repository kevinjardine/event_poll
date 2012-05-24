<?php
// generate a list of filter tabs
// TODO: adapt this for event polls
$filter_context = $vars['filter'];
$url_start = "event_poll/list";

$tabs = array(
	'all' => array(
		'name' => 'all',
		'text' => elgg_echo('event_poll:list:show_all'),
		'href' => "$url_start/all",
		'selected' => ($filter_context == 'all'),
		'priority' => 200,
	),
);
$tabs ['mine'] = array(
	'name' => 'mine',
	'text' => elgg_echo('event_poll:list:show_mine'),
	'href' => "$url_start/mine",
	'selected' => ($filter_context == 'mine'),
	'priority' => 300,
);
$tabs['other'] = array(
	'name' => 'other',
	'text' => elgg_echo('event_poll:list:show_other'),
	'href' =>  "$url_start/other",
	'selected' => ($filter_context == 'other'),
	'priority' => 400,
);

foreach ($tabs as $name => $tab) {
	elgg_register_menu_item('filter', $tab);
}

echo elgg_view_menu('filter', array('sort_by' => 'priority', 'class' => 'elgg-menu-hz'));
