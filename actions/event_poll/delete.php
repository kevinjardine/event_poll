<?php
// a variation of the event calendar delete that always forwards back to the referer
$event_guid = get_input('guid',0);
$event = get_entity($event_guid);
if (elgg_instanceof($event,'object','event_calendar') && $event->canEdit()) {
	if (get_input('cancel','')) {
		system_message(elgg_echo('event_calendar:delete_cancel_response'));
	} else {
		$container = get_entity($event->container_guid);
		$event->delete();
		system_message(elgg_echo('event_calendar:delete_response'));
	}
} else {
	register_error(elgg_echo('event_calendar:error_delete'));
}

forward(REFERER);
