<?php
elgg_load_library('elgg:event_poll');
$current_user = elgg_get_logged_in_user_entity();
$guid = get_input('event_guid');
$message = trim(get_input('message',''));
$event = get_entity($guid);
if (check_entity_relationship($current_user->guid, 'event_poll_invitation',$guid) && elgg_instanceof($event,'object','event_calendar') && $event->event_poll) {
	elgg_delete_annotations(array('guid'=>$guid,'annotation_name'=>'event_poll_vote','annotation_owner_guid' => $current_user->guid,'limit' => 0));
	$poll_options = event_poll_get_options($event);
	foreach($poll_options as $option) {
		$tick = get_input($option);
		if ($tick) {
			create_annotation($guid,'event_poll_vote',$option,NULL,$current_user->guid,ACCESS_PUBLIC);
		}
	}
	add_entity_relationship($current_user->guid,'event_poll_voted',$guid);
	if ($message && $message != elgg_echo('event_poll:vote_message:explanation')) {
		$site = elgg_get_site_entity();
		$message = elgg_echo('event_poll:vote_message:top',array($current_user->name, $current_user->username))."\n\n".$message;
		notify_user($event->owner_guid,$site->guid,elgg_echo('event_poll:vote_message:subject',array($event->title)),$message,NULL,'email');
	}
	system_message(elgg_echo('event_poll:vote_response:success'));
	forward($event->getURL());	
} else {
	register_error(elgg_echo('event_poll:vote_response:error'));
}

forward();
