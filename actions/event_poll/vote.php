<?php
elgg_load_library('elgg:event_poll');
$guid = get_input('event_guid');
$event = get_entity($guid);
$message = trim(get_input('message',''));
$schedule_slot = get_input('schedule_slot');

if(event_poll_vote($event,$message,$schedule_slot)) {
	if ($schedule_slot)	{
		system_message(elgg_echo('event_poll:schedule:response'));
	} else {
		system_message(elgg_echo('event_poll:vote_response:success'));
	}
	forward($event->getURL());
} else {
	register_error(elgg_echo('event_poll:error_event_poll_edit'));
	forward();
}

forward();
