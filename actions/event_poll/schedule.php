<?php
$guid = get_input('event_guid');
$schedule_slot = get_input('schedule_slot');

$event = get_entity($guid);
if (elgg_instanceof($event,'object','event_calendar') && $event->canEdit()) {
	@list($iso,$time) = explode('__',$schedule_slot);
	
	// currently assumes that all poll events last one hour until this issue is fixed
	$event->start_time = $time;
	$event->end_time = $time+60;
	$event->end_date = strtotime($iso);
	$event->start_date = strtotime("+ $time minutes",strtotime($iso));
	$event->real_end_time = strtotime("+ 1 hour",$event->start_date);
	
	system_message(elgg_echo('event_poll:schedule:response'));	
	forward($event->getURL());
} else {
	register_error(elgg_echo('event_poll:error_event_poll_edit'));
	forward();
}