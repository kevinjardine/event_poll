<?php
$event = $vars['event'];

$message_options = array(
	elgg_echo('event_poll:schedule_message:options:all') => 'all',
	elgg_echo('event_poll:schedule_message:options:not_responded') => 'not_responded',
);

echo elgg_view('input/hidden',array('name'=>'event_guid','value'=>$event->guid));

$html = '<div id="event-poll-schedule-message-wrapper">';
$html .= '<h3>'.elgg_echo('event_poll:schedule_message:subtitle').'</h3>';
$html .= '<br />';
$html .= '<label>'.elgg_echo('event_poll:schedule_message:options:label').'</label>';
$html .= elgg_view('input/radio',array('name' => 'message_option','options'=>$message_options,'id'=>'event-poll-schedule-options','value'=>'all'));
$html .= '<br /><br />';
$html .= '<label>'.elgg_echo('event_poll:schedule_message:message:label').'</label>';
$html .= elgg_view('input/plaintext',array('id'=>'event-poll-schedule-message','name'=>'message'));
$html .= elgg_view('input/submit',array('id'=>'event-poll-schedule-message-button','value' => elgg_echo('event_poll:schedule_message_button')));
$html .= '</div>';

echo $html;
		