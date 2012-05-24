<?php
$hours = array();
for ($i=0;$i<24;$i++) {
	$hours[$i] = $i;
}

$minutes = array();
for ($i=0;$i<60;$i += 5) {
	if ($i < 10) {
		$minutes[$i] = '0'.$i;
	} else {
		$minutes[$i] = $i;
	}
}
echo elgg_view('input/dropdown',array('id'=>'event-poll-length-hour','name'=>'hour_length','value'=>1,'options_values'=>$hours));
echo elgg_echo('event_poll:hours_and');
echo elgg_view('input/dropdown',array('id'=>'event-poll-length-minute','name'=>'minute_length','value'=>0,'options_values'=>$minutes));
echo elgg_echo('event_poll:minutes');
