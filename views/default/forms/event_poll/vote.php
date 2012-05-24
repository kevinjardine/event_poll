<?php
elgg_load_library('elgg:event_poll');
$event = $vars['event'];
$owner = $event->getOwnerEntity();
echo '<h3>'.elgg_echo('event_poll:vote_subtitle',array($event->title, $owner->name)).'</h3>';
echo '<p>'.elgg_echo('event_poll:vote_explanation').'</p>';
if ($event->event_poll) {
	$event_poll = unserialize($event->event_poll);
	if(is_array($event_poll) && count($event_poll) > 0) {
		echo elgg_view('input/hidden',array('name'=>'event_guid','value'=>$event->guid));
		$current_user = elgg_get_logged_in_user_entity();
		$times_choices = event_poll_get_times($event->guid);
		$invitees = event_poll_get_invitees($event->guid);
		$voted_guids = event_poll_get_voted_guids($event->guid);
		$current_schedule_slot = event_poll_get_current_schedule_slot($event);
		
		$table_rows = event_poll_display_vote_table_header($event_poll);
				
		@list($table_extra, $others) = event_poll_display_invitees($event_poll,$times_choices,$invitees,$voted_guids,$current_user->guid);
		$table_rows .= $table_extra;
		
		// current user		
		$table_rows .= '<tr><td class="event-poll-name-td">' .$current_user->name.'</td>';
		foreach ($event_poll as $iso => $date) {
			foreach($date['times'] as $time) {
				if ($time == '-') {
					$table_rows .= '<td class="event-poll-vote-current-td">&nbsp</td>';
				} else {
					$name = "{$iso}__{$time}";
					if (isset($times_choices[$current_user->guid]) && in_array($name,$times_choices[$current_user->guid])) {
						$table_rows .= '<td class="event-poll-vote-current-td">'.elgg_view('input/checkbox',array('class'=>'event-poll-vote-checkbox','name'=>$name,'value'=>1,'checked'=>'checked')).'</td>';
					} else {
						$table_rows .= '<td class="event-poll-vote-current-td">'.elgg_view('input/checkbox',array('class'=>'event-poll-vote-checkbox','name'=>$name,'value'=>1)).'</td>';
					}
				}
			}
		}
		// add the none option
		$table_rows .= '<td class="event-poll-vote-current-td">'.elgg_view('input/checkbox',array('class'=>'event-poll-vote-none-checkbox','name'=>'none','value'=>1)).'</td>';
		$table_rows .= '</tr>';
		if ($event->canEdit()) {
			// schedule bit	
			$table_rows .= '<tr><td class="event-poll-name-td">' .elgg_echo('event_poll:choose_time').'</td>';
			foreach ($event_poll as $iso => $date) {
				foreach($date['times'] as $time) {
					if ($time == '-') {
						$table_rows .= '<td class="event-poll-vote-current-td">&nbsp</td>';
					} else {
						$value = "{$iso}__{$time}";
						$table_rows .= '<td class="event-poll-vote-current-td">';
						if ($current_schedule_slot == $value) {
							$table_rows .= '<input type="radio" name="schedule_slot" value="'.$value.'" checked="checked">';
						} else {
							$table_rows .= '<input type="radio" name="schedule_slot" value="'.$value.'">';
						}
						$table_rows .= '</td>';
					}
				}
			}
			// add the none option
			$table_rows .= '<td>&nbsp;</td>';
			$table_rows .= '</tr>';
		}
		$table = '<table id="event-poll-vote-table">';	
		$table .= $table_rows . '</table>';
		echo $table;
		
		// other invitees
		if ($others) {
			echo '<div id="event-poll-vote-others-wrapper">';
			echo '<p>'.elgg_echo('event_poll:vote:other').'</p>';
			foreach($others as $o) {
				echo '<p>'.$o->name.'</p>';
			}
			echo '</div>';
		}
		
		if ($event->canEdit()) {
			// This extra stuff appears only if a time for the event has been selected
			$html = '<div id="event-poll-vote-event-data-wrapper">';
			$html .= elgg_view('event_calendar/reminder_section',$vars);
	
			$html .= elgg_view('event_calendar/personal_manage_section',$vars);
			$html .= elgg_view('event_calendar/share_section',$vars);
			$html .= '</div>';
		} else {
			$html .= '<div id="event-poll-vote-message-wrapper">';
			$html .= '<label>'.elgg_echo('event_poll:vote_message:label').'</label>';
			$html .= elgg_view('input/plaintext',array('id'=>'event-poll-vote-message','name'=>'message','value' => elgg_echo('event_poll:vote_message:explanation')));
			$html .= '</div>';
		}
		
		$html .= '<div id="event-poll-vote-button-wrapper">';
		$html .= elgg_view('input/submit',array('value' => elgg_echo('event_poll:vote_button')));
		$html .= '</div>';
		
		echo $html;
	}
}