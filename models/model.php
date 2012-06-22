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
		$body_vars['form_data'] = event_poll_prepare_vote_form_vars($event);
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
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');

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

function event_poll_get_response_time($event_guid,$user_guid=0) {
	if (!$user_guid) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	$options= array(
		'guid' => $event_guid,
		'annotation_name' => 'event_poll_vote',
		'owner_guid' => $user_guid,
		'limit' => 1,
	);
	$annotations = elgg_get_annotations($options);
	if ($annotations) {
		return $annotations[0]->time_created;
	} else {
		return 0;
	}
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
	//$keys = array_keys($event_poll);
	//$num_times = count($event_poll[$keys[0]]['times']);
	$table_rows = '<tr><td class="event-poll-extra-td">&nbsp;</td>';
	$table_header = '<tr><td class="event-poll-extra-td">&nbsp;</td>';
	$i = 0;
	foreach ($event_poll as $iso => $date) {
		$num_times = count($date['times']);
		$table_header .= '<td class="event-poll-vote-date-td-header event-poll-vote-date-td" colspan="'.$num_times.'">'.$date['human_date'].'</td>';
		$j = 0;
		foreach($date['human_times'] as $time) {
			if ($j == 0) {
				$table_rows .= '<td class="event-poll-left-td">'.$time.'</td>';
			} else if ($j == $num_times - 1) {
				$table_rows .= '<td class="event-poll-right-td">'.$time.'</td>';
			} else {
				$table_rows .= '<td>'.$time.'</td>';
			}
			$j += 1;
		}
		
		$i += 1;
	}
	$table_header .= '<td class="event-poll-vote-date-td-header event-poll-vote-none-td1">'.elgg_echo('event_poll:none_of_these1').'</td>'; 
	$table_header .= '</tr>';
	$table_rows .= '<td class="event-poll-vote-date-td-header event-poll-vote-none-td2">&nbsp;</td>';
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
			// add the none bit
			$name = "none";
			if (isset($times_choices[$user->guid]) && in_array($name,$times_choices[$user->guid])) {
				$table_rows .= '<td class="event-poll-vote-internal-td event-poll-check-image">';
				$table_rows .= elgg_view('input/checkbox',array('value'=>1,'checked'=>'checked','disabled'=>'disabled'));
				$table_rows .= '</td>';
			} else {
				$table_rows .= '<td class="event-poll-vote-internal-td">&nbsp;</td>';
			}
		} else if ($user->guid != $current_user_guid) {
			$others[] = $user;
		}
		$table_rows .= '</tr>';
	}
	
	return array($table_rows,$others);
}

function event_poll_get_page_content_list($filter) {
	elgg_load_library('elgg:event_calendar');
	//event_calendar_handle_event_poll_add_items();
	$filter_override = elgg_view('event_poll/filter_menu',array('filter' => $filter));
	$options = array(
		'type' => 'object',
		'subtype' => 'event_calendar',
		'metadata_name_value_pairs' => array(array('name'=>'schedule_type','value'=>'poll')),
		'full_view' => FALSE,
	);
	if ($filter == 'all') {
		$title = elgg_echo('event_poll:list:title:show_all');
	} else if ($filter == 'mine') {
		$title = elgg_echo('event_poll:list:title:show_mine');
		$options['owner_guid'] = elgg_get_logged_in_user_guid();
	} else {
		$title = elgg_echo('event_poll:list:title:show_others');
		$options['wheres'] = array('e.owner_guid !=  '.elgg_get_logged_in_user_guid());
	}
	
	$content = elgg_list_entities($options,'elgg_get_entities_from_metadata','event_poll_list_polls');
	
	if ($content) {
		$subject_header = elgg_echo('event_poll:listing:header:subject');
		$requester_header = elgg_echo('event_poll:listing:header:requester');
		$date_header = elgg_echo('event_poll:listing:header:date');
		$responded_header = elgg_echo('event_poll:listing:header:responded');
		
		$header_bit = <<<HTML
		<div class="event-poll-listing-header-wrapper">
			<div class="event-poll-listing-header-subject">$subject_header</div>
			<div class="event-poll-listing-header-requester">$requester_header</div>
			<div class="event-poll-listing-header-date">$date_header</div>
			<div class="event-poll-listing-header-responded">$responded_header</div>
		</div>
HTML;
		$content = $header_bit.$content;
	} else {
		$content = elgg_echo('event_poll:listing:no_polls');
	}

	elgg_push_breadcrumb(elgg_echo('event_calendar:show_events_title'),'event_calendar/list');
	elgg_push_breadcrumb($title);
	
	$params = array('title' => $title, 'content' => $content,'filter_override' => $filter_override);

	$body = elgg_view_layout("content", $params);

	return elgg_view_page($title,$body);
}

function event_poll_list_polls($es) {
	$r = '';
	foreach($es as $e) {
		$r .= elgg_view('event_poll/list_poll',array('event'=>$e));
	}
	
	return $r;
}

function event_poll_vote($event,$message='',$schedule_slot='') {
	if (elgg_instanceof($event,'object','event_calendar')) {
		if ($event->canEdit()) {
			if ($schedule_slot) {
				@list($iso,$time) = explode('__',$schedule_slot);
				
				$event->start_time = $time;
				$event->end_time = $time+$event->event_length;
				$event->end_date = strtotime($iso);
				$event->start_date = strtotime("+ $time minutes",strtotime($iso));
				$event->real_end_time = strtotime("+ ".$event->event_length." minutes",$event->start_date);
				$event->is_event_poll = 0;
			}
			$event_calendar_personal_manage = elgg_get_plugin_setting('personal_manage', 'event_calendar');
		
			if ($event_calendar_personal_manage == 'by_event') {
				$event->personal_manage = get_input('personal_manage');
			}
			
			$event->access_id = get_input('access_id');
			$event->send_reminder = get_input('send_reminder');
			$event->reminder_number = get_input('reminder_number');
			$event->reminder_interval = get_input('reminder_interval');
			
			$event->save();
		}
		
		$current_user = elgg_get_logged_in_user_entity();
		
		if (check_entity_relationship($current_user->guid, 'event_poll_invitation',$event->guid) && $event->event_poll) {
			elgg_delete_annotations(array('guid'=>$event->guid,'annotation_name'=>'event_poll_vote','annotation_owner_guid' => $current_user->guid,'limit' => 0));
			$poll_options = event_poll_get_options($event);
			foreach($poll_options as $option) {
				$tick = get_input($option);
				if ($tick) {
					create_annotation($event->guid,'event_poll_vote',$option,NULL,$current_user->guid,ACCESS_PUBLIC);
				}
			}
			add_entity_relationship($current_user->guid,'event_poll_voted',$event->guid);
			if ($message && $message != elgg_echo('event_poll:vote_message:explanation')) {
				$site = elgg_get_site_entity();
				$message = elgg_echo('event_poll:vote_message:top',array($current_user->name, $current_user->username))."\n\n".$message;
				notify_user($event->owner_guid,$site->guid,elgg_echo('event_poll:vote_message:subject',array($event->title)),$message,NULL,'email');
			}	
		}
		return TRUE;
	} else {
		return FALSE;
	}
}

function event_poll_prepare_vote_form_vars($event) {

	// input names => defaults
	$values = array(
		'send_reminder' => NULL,
		'reminder_number' => 1,
		'reminder_interval' => 60,
		'personal_manage' => 'open',
		'access_id' => ACCESS_DEFAULT,
	);

	foreach (array_keys($values) as $field) {
		if (isset($event->$field)) {
			$values[$field] = $event->$field;
		}
	}

	if (elgg_is_sticky_form('event_poll')) {
		$sticky_values = elgg_get_sticky_values('event_poll');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('event_poll');

	return $values;
}

function event_poll_get_current_schedule_slot($event) {
	if ($event->start_date) {
		$iso = date('Y-m-d',$event->start_date);
		$time = $event->start_time;
		return "{$iso}__{$time}";
	} else {
		return '';
	}
}

function elgg_poll_set_poll($guid,$poll,$event_length) {
	$event = get_entity($guid);
	if (elgg_instanceof($event,'object','event_calendar') && $event->canEdit()) {
		$start_time = 2000000000;
		$end_time = 0;
		foreach($poll as $iso_date => $data) {
			$ds = strtotime($iso_date);
			foreach($data['times'] as $t) {
				$ts = strtotime("+ $t minutes",$ds);
				if ($start_time > $ts) {
					$start_time = $ts;
				}
				
				if ($end_time < $ts) {
					$end_time = $ts;
				}
			}
		}
		
		// sort the poll by time within date
		
		
		$event->event_poll = serialize($poll);
		$event->event_length = $event_length;
		$event->event_poll_start_time = $start_time;
		$event->event_poll_end_time = $end_time+60*$event_length;
		$event->is_event_poll = 1;
	}
	
	return '';
}

function event_poll_merge_poll_events($events, $start_time,$end_time) {
	$options = array(
		'type'=>'object',
		'subtype' => 'event_calendar',
		'metadata_name_value_pairs' => 	array(array('name'=>'is_event_poll','value'=>1),
										array('name'=>'event_poll_start_time','value'=>$start_time,'operand'=>'>='),
										array('name'=>'event_poll_start_time','value'=>$end_time,'operand'=>'<=')
		),
		'limit' => 0,
	);
	$eps = elgg_get_entities_from_metadata($options);
	foreach($eps as $e) {
		$event_length = $e->event_length;
		$p = unserialize($e->event_poll);
		$data = array();
		foreach($p as $iso_date => $times_data) {
			$dts = strtotime($iso_date);
			foreach($times_data['times'] as $m) {
				$ts = strtotime("+ $m minutes",$dts);
				$data[] = array('start_time' => $ts, 'end_time' => $ts+60*$event_length);
			}
		}
		$events[] = array('event' => $e,'is_event_poll'=>TRUE,'data' => $data);
	}
	
	return $events;
}

function event_poll_handle_event_poll_add_items($group_guid=0) {
	if ($group_guid) {	
		$url_add_event =  "event_calendar/add/$group_guid";
		$url_schedule_event =  "event_calendar/schedule/$group_guid";
	} else {
		$url_add_event =  "event_calendar/add";
		$url_schedule_event =  "event_calendar/schedule";		
	}
	$url_list_polls =  "event_poll/list/all";
	
	$item = new ElggMenuItem('event-calendar-0add', elgg_echo('event_calendar:add_event'), $url_add_event);
	$item->setSection('event_poll');
	elgg_register_menu_item('page', $item);
	
	$item = new ElggMenuItem('event-calendar-1schedule', elgg_echo('event_calendar:schedule_event'), $url_schedule_event);
	$item->setSection('event_poll');
	elgg_register_menu_item('page', $item);
	
	$item = new ElggMenuItem('event-calendar-2list-polls', elgg_echo('event_calendar:list_polls'), $url_list_polls);
	$item->setSection('event_poll');
	elgg_register_menu_item('page', $item);
}

