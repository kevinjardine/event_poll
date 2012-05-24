<?php
$guid = get_input('event_guid');
$schedule_slot = get_input('schedule_slot');

if(event_poll_vote($guid,$schedule_slot)) {	
	system_message(elgg_echo('event_poll:schedule:response'));	
	forward($event->getURL());
} else {
	register_error(elgg_echo('event_poll:error_event_poll_edit'));
	forward();
}