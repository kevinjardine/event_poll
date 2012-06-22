<?php $e = $vars['event']; ?>
<div class="event-poll-listing-item-wrapper">
<div class="event-poll-listing-subject">
<?php echo elgg_view('output/url', array(
	'href' => 'event_poll/vote/'.$e->guid,
	'text' => $e->title,
));?>
</div>
<div class="event-poll-listing-requester">
<?php 
$oe = $e->getOwnerEntity();
echo elgg_view('output/url', array(
	'href' => $oe->getURL(),
	'text' => $oe->name,
));?></div>
<div class="event-poll-listing-date">
<?php
	echo elgg_get_friendly_time($e->time_created);
?>
</div>
<div class="event-poll-listing-response">
<?php
elgg_load_library('elgg:event_poll');
$time_responded = event_poll_get_response_time($e->guid);
if ($time_responded) {
	echo elgg_get_friendly_time($time_responded);
} else {
	echo '&nbsp;';
}
?>
</div>
</div>