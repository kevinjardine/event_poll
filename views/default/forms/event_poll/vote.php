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

		/*$keys = array_keys($event_poll);
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
		$table_rows .= '</tr>';*/
		
		$table_rows = event_poll_display_vote_table_header($event_poll);
		
		/*foreach($invitees as $user) {
			if (in_array($user->guid, $voted_guids) && $user->guid != $current_user->guid) {
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
			} else if ($user->guid != $current_user->guid) {
				$others[] = $user;
			}
			$table_rows .= '</tr>';
		}*/
		
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
						$table_rows .= '<td class="event-poll-vote-current-td">'.elgg_view('input/checkbox',array('name'=>$name,'value'=>1,'checked'=>'checked')).'</td>';
					} else {
						$table_rows .= '<td class="event-poll-vote-current-td">'.elgg_view('input/checkbox',array('name'=>$name,'value'=>1)).'</td>';
					}
				}
			}
		}
		$table_rows .= '</tr>';
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

		$html = '<div id="event-poll-vote-message-wrapper">';
		$html .= '<label>'.elgg_echo('event_poll:vote_message:label').'</label>';
		$html .= elgg_view('input/plaintext',array('id'=>'event-poll-vote-message','name'=>'message','value' => elgg_echo('event_poll:vote_message:explanation')));
		$html .= '</div>';
		
		$html .= '<div id="event-poll-vote-button-wrapper">';
		$html .= elgg_view('input/submit',array('value' => elgg_echo('event_poll:vote_button')));
		$html .= '</div>';
		
		echo $html;
	}
}