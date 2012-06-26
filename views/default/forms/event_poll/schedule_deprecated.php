<?php
elgg_load_library('elgg:event_poll');

echo '<h3>'.elgg_echo('event_poll:schedule_subtitle').'</h3>';
$event = $vars['event'];
if ($event->event_poll) {
	$event_poll = unserialize($event->event_poll);
	if(is_array($event_poll) && count($event_poll) > 0) {
		echo elgg_view('input/hidden',array('name'=>'event_guid','value'=>$event->guid));
		$times_choices = event_poll_get_times($event->guid);
		$invitees = event_poll_get_invitees($event->guid);
		$voted_guids = event_poll_get_voted_guids($event->guid);
		
		// display vote table		
		$table_rows = event_poll_display_vote_table_header($event_poll);
		@list($table_extra, $others) = event_poll_display_invitees($event_poll,$times_choices,$invitees,$voted_guids,0);
		$table_rows .= $table_extra;
		
		// schedule bit	
		$table_rows .= '<tr><td class="event-poll-name-td">' .elgg_echo('event_poll:choose_time').'</td>';
		foreach ($event_poll as $iso => $date) {
			foreach($date['times'] as $time) {
				if ($time == '-') {
					$table_rows .= '<td class="event-poll-vote-current-td">&nbsp</td>';
				} else {
					$value = "{$iso}__{$time}";
					$table_rows .= '<td class="event-poll-vote-current-td">';
					$table_rows .= '<input type="radio" name="schedule_slot" value="'.$value.'">';
					$table_rows .= '</td>';
				}
			}
		}
		$table_rows .= '</tr>';
		echo '<table id="event-poll-vote-table">' . $table_rows . '</table>';
		
		// other invitees
		if ($others) {
			echo '<div id="event-poll-vote-others-wrapper">';
			echo '<p>'.elgg_echo('event_poll:vote:other').'</p>';
			foreach($others as $o) {
				echo '<p>'.$o->name.'</p>';
			}
			echo '</div>';
		}
		
		$html = '<div id="event-poll-schedule-button-wrapper">';
		$html .= elgg_view('input/submit',array('id'=>'event-poll-schedule-button','value' => elgg_echo('event_poll:schedule_button')));
		$html .= '</div>';
		
		echo $html;
	}
}