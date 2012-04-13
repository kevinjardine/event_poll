<?php
elgg_load_library('elgg:event_poll');

$guid = get_input('guid');
$subject = get_input('subject');
$body = get_input('body');
$invitees = get_input('invitees');

if (event_poll_send_invitations($guid,$subject,$body,$invitees)) {
	$result = array('success'=>TRUE,'msg'=>elgg_echo('event_poll:send_invitations:success'));
} else {
	$result = array('success'=>FALSE, 'msg'=>elgg_echo('event_poll:send_invitations:error'));
}

echo json_encode($result);
