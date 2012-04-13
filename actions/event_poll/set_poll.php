<?php
$guid = get_input('guid');
$poll = get_input('poll');
$event = get_entity($guid);
if (elgg_instanceof($event,'object','event_calendar') && $event->canEdit()) {
	$event->event_poll = serialize($poll);
}

echo '';
