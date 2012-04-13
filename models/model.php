<?php
function event_poll_get_page_content_vote($guid) {
	elgg_load_js('elgg.event_poll');
	$vars = array();
	$vars['id'] = 'event-poll-vote';
	$vars['name'] = 'event_poll_vote';
	// just in case a feature adds an image upload
	$vars['enctype'] = 'multipart/form-data';

	$body_vars = array();
	$event = get_entity((int)$guid);
	if (elgg_instanceof($event, 'object', 'event_calendar')) {
		$body_vars['event'] = $event;
		$event_container = get_entity($event->container_guid);
		if (elgg_instanceof($event_container, 'group')) {
			elgg_push_breadcrumb(elgg_echo('event_calendar:group_breadcrumb'), 'event_calendar/group/'.$event->container_guid);
		} else {
			elgg_push_breadcrumb(elgg_echo('event_calendar:show_events_title'),'event_calendar/list');
		}
		elgg_push_breadcrumb($event->title,$event->getURL());

		$title = elgg_echo('event_poll:vote_title');
		elgg_push_breadcrumb(elgg_echo('event_poll:vote_title'));	
		$content = elgg_view_form('event_poll/vote', $vars,$body_vars);
	} else {
		$content = elgg_echo('event_poll:error_event_poll_edit');
	}
	
	$params = array('title' => $title, 'content' => $content,'filter' => '');

	$body = elgg_view_layout("content", $params);

	return elgg_view_page($title,$body);	
}

function event_poll_get_page_content_schedule($guid) {
	elgg_load_js('elgg.event_poll');
	$vars = array();
	$vars['id'] = 'event-poll-schedule';
	$vars['name'] = 'event_poll_schedule';
	// just in case a feature adds an image upload
	$vars['enctype'] = 'multipart/form-data';

	$body_vars = array();
	$event = get_entity((int)$guid);
	if (elgg_instanceof($event, 'object', 'event_calendar') && $event->canEdit()) {
		$body_vars['event'] = $event;
		$event_container = get_entity($event->container_guid);
		if (elgg_instanceof($event_container, 'group')) {
			elgg_push_breadcrumb(elgg_echo('event_calendar:group_breadcrumb'), 'event_calendar/group/'.$event->container_guid);
		} else {
			elgg_push_breadcrumb(elgg_echo('event_calendar:show_events_title'),'event_calendar/list');
		}
		elgg_push_breadcrumb($event->title,$event->getURL());

		$title = elgg_echo('event_poll:schedule_title',array($event->title));
		elgg_push_breadcrumb($title);	
		$content = elgg_view_form('event_poll/schedule', $vars,$body_vars);
		$content .= elgg_view_form('event_poll/schedule_message', $vars,$body_vars);
	} else {
		$content = elgg_echo('event_poll:error_event_poll_edit');
	}
	
	$params = array('title' => $title, 'content' => $content,'filter' => '');

	$body = elgg_view_layout("content", $params);

	return elgg_view_page($title,$body);	
}

function event_poll_get_page_content_edit($page_type,$guid) {
	elgg_load_js('elgg.event_poll');
	$vars = array();
	$vars['id'] = 'event-poll-edit';
	$vars['name'] = 'event_poll_edit';
	// just in case a feature adds an image upload
	$vars['enctype'] = 'multipart/form-data';

	$body_vars = array();
	$event = get_entity((int)$guid);
	if (elgg_instanceof($event, 'object', 'event_calendar') && $event->canEdit()) {
		$body_vars['event'] = $event;
		$body_vars['form_data'] =  event_poll_prepare_edit_form_vars($event);
		// start date is the start of the month for this event
		$body_vars['start_date'] = gmdate("Y-m",$event->start_date)."-1";
		
		$event_container = get_entity($event->container_guid);
		if (elgg_instanceof($event_container, 'group')) {
			elgg_push_breadcrumb(elgg_echo('event_calendar:group_breadcrumb'), 'event_calendar/group/'.$event->container_guid);
		} else {
			elgg_push_breadcrumb(elgg_echo('event_calendar:show_events_title'),'event_calendar/list');
		}
		elgg_push_breadcrumb($event->title,$event->getURL());

		if ($page_type == 'edit') {
			$title = elgg_echo('event_poll:edit_title');
			elgg_push_breadcrumb(elgg_echo('event_poll:edit_title'));	
			$content = elgg_view_form('event_poll/edit', $vars,$body_vars);
		} else {
			$title = elgg_echo('event_poll:add_title');
			elgg_push_breadcrumb(elgg_echo('event_poll:add_title'));	
			$content = elgg_view_form('event_poll/edit', $vars, $body_vars);
		}
		
	} else {
		$title = elgg_echo('event_poll:error_title');
		$content = elgg_echo('event_poll:error_event_poll_edit');
	}

	$params = array('title' => $title, 'content' => $content,'filter' => '');

	$body = elgg_view_layout("content", $params);

	return elgg_view_page($title,$body);
}

function event_poll_prepare_edit_form_vars($event) {
	// TODO: add content here
	return array();
}

function event_poll_get_times_dropdown() {
	return elgg_view('input/timepicker',array('name'=>'event_poll_time','value'=>''));
}

function event_poll_send_invitations($guid,$subject,$body,$invitees) {
	$event = get_entity($guid);
	if (elgg_instanceof($event,'object','event_calendar') && $event->canEdit()) {
		$site = elgg_get_site_entity();
		$body .= "\n\n".elgg_get_site_url().'event_poll/vote/'.$guid;
		if (is_array($invitees) && count($invitees) > 0) {
			foreach($invitees as $user_guid) {
				add_entity_relationship($user_guid,'event_poll_invitation',$guid);
			}
			// email invitees
			notify_user($invitees,$site->guid,$subject,$body,NULL,'email');
			return TRUE;
		}
	}	
	return FALSE;
}

function event_poll_get_options($event) {
	$options = array();
	if ($event->event_poll) {
		$event_poll = unserialize($event->event_poll);
		foreach($event_poll as $iso => $date) {
			foreach($date['times'] as $time) {
				$options[] = "{$iso}__{$time}";
			}
		}
	}
	return $options;
}

function event_poll_get_times($event_guid) {
	$times = array();
	$options= array(
		'guid' => $event_guid,
		'annotation_name' => 'event_poll_vote',
		'limit' => 0,
	);
	$annotations = elgg_get_annotations($options);
	foreach($annotations as $a) {
		if(!isset($times[$a->owner_guid])) {
			$times[$a->owner_guid] = array();
		}
		$times[$a->owner_guid][] = $a->value;
	}
	
	return $times;
}

function event_poll_get_invitees($event_guid) {
	$invitees = array();
	$options = array(
		'type' => 'user',
		'relationship' => 'event_poll_invitation',
		'relationship_guid' => $event_guid,
		'inverse_relationship' => TRUE,
		'limit' => 0,
	);
	return elgg_get_entities_from_relationship($options);
}

function event_poll_get_voted_guids($event_guid) {
	$voted = array();
	$options = array(
		'type' => 'user',
		'relationship' => 'event_poll_voted',
		'relationship_guid' => $event_guid,
		'inverse_relationship' => TRUE,
		'limit' => 0,
	);
	$users = elgg_get_entities_from_relationship($options);
	foreach($users as $u) {
		$voted[] = $u->guid;
	}
	return $voted;
}

// displays a vote table header for an event poll
function event_poll_display_vote_table_header($event_poll) {
	$keys = array_keys($event_poll);
	$num_times = count($event_poll[$keys[0]]['times']);
	$table_rows = '<tr><td class="event-poll-extra-td">&nbsp;</td>';
	$table_header = '<tr><td class="event-poll-extra-td">&nbsp;</td>';
	$i = 0;
	foreach ($event_poll as $iso => $date) {
		if ($i != 0 && $i != $num_times - 1) {
			$table_header .= '<td class="event-poll-vote-date-td" colspan="'.$num_times.'">'.$date['human'].'</td>';
		} else {
			$table_header .= '<td colspan="'.$num_times.'">'.$date['human'].'</td>';
		}
		$j = 0;
		foreach($date['human_times'] as $time) {
			if ($i != 0 && $i != $num_times - 1) {
				if ($j == 0) {
					$table_rows .= '<td class="event-poll-left-td">'.$time.'</td>';
				} else if ($j == $num_times - 1) {
					$table_rows .= '<td class="event-poll-right-td">'.$time.'</td>';
				} else {
					$table_rows .= '<td>'.$time.'</td>';
				}
			} else {
				$table_rows .= '<td>'.$time.'</td>';
			}
			$j += 1;
		}
		
		$i += 1;
	}
	$table_header .= '</tr>';
	$table_rows .= '</tr>';
	
	return $table_header . $table_rows;
}

// displays a table fragment for invitees who have voted
function event_poll_display_invitees($event_poll,$times_choices,$invitees,$voted_guids,$current_user_guid) {
	$table_rows = '';
	$others = array();
	foreach($invitees as $user) {
		if (in_array($user->guid, $voted_guids) && $user->guid != $current_user_guid) {
			$table_rows .= '<tr><td class="event-poll-name-td">' .$user->name.'</td>';
			foreach ($event_poll as $iso => $date) {
				foreach($date['times'] as $time) {
					if ($time == '-') {
						$table_rows .= '<td class="event-poll-vote-internal-td">&nbsp;</td>';
					} else {
						$name = "{$iso}__{$time}";
						if (isset($times_choices[$user->guid]) && in_array($name,$times_choices[$user->guid])) {
							$table_rows .= '<td class="event-poll-vote-internal-td event-poll-check-image">';
							$table_rows .= elgg_view('input/checkbox',array('value'=>1,'checked'=>'checked','disabled'=>'disabled'));
							$table_rows .= '</td>';
						} else {
							$table_rows .= '<td class="event-poll-vote-internal-td">&nbsp;</td>';
						}
					}
				}
			}
		} else if ($user->guid != $current_user_guid) {
			$others[] = $user;
		}
		$table_rows .= '</tr>';
	}
	
	return array($table_rows,$others);
}
