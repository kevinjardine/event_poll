<?php
elgg_load_js('elgg.event_poll');
elgg_load_js('elgg.full_calendar');
$event = $vars['event'];
$container = get_entity($event->container_guid);
if (elgg_instanceof($container,'group')) {
	$container_title = $container->name;
} else {
	$container_title = elgg_echo('event_calendar:site_calendar');
}

$invitation_subject = elgg_echo('event_poll:invitation_subject', array($event->title));
$invitation_body = elgg_echo('event_poll:invitation_body');
$event_calendar_time_format = elgg_get_plugin_setting('timeformat', 'event_calendar');
if (!$event_calendar_time_format) {
	$event_calendar_time_format = 12;
}
?>
<div class="event-poll-info-wrapper">
<div class="event-poll-info-item"><label><?php echo elgg_echo('event_calendar:title_label')?></label><span><?php echo $event->title; ?></span></div>
<div class="event-poll-info-item"><label><?php echo elgg_echo('event_calendar:venue_label')?></label><span><?php echo $event->venue; ?></span></div>
<div class="event-poll-info-item"><label><?php echo elgg_echo('event_calendar:calendar_label')?></label><span><?php echo $container_title; ?></span></div>
</div>
<div id="event-poll-stage1-wrapper">
<h2><?php echo elgg_echo('event_poll:select_length:title'); ?></h2>
<?php echo elgg_view('event_poll/event_length'); ?>
</div>
<h2 id ="event-poll-title1"><?php echo elgg_echo('event_poll:select_days:title'); ?></h2>
<h2 id ="event-poll-title2"><?php echo elgg_echo('event_poll:select_times:title'); ?></h2>
<h2 id ="event-poll-title3"><?php echo elgg_echo('event_poll:days_and_times:title').' <a id="event-poll-edit-link" href="javascript:void(0)">'.elgg_echo('event_poll:edit').'</a>'; ?></h2>
<input type="hidden" id="event-poll-event-guid" value="<?php echo $event->guid; ?>" />
<?php echo elgg_view('input/hidden', array('id'=>'event-poll-event-title','value'=>$event->title)); ?>
<?php echo elgg_view('input/hidden', array('id'=>'event-poll-event-url','value'=>$event->getURL())); ?>
<input type="hidden" id="event-poll-month-number" value="<?php echo date('n')-1; ?>" />
<input type="hidden" id="event-poll-hour-number" value="<?php echo date('G'); ?>" />
<input type="hidden" id="event-poll-time-format" value="<?php echo $event_calendar_time_format; ?>" />
<input type="hidden" id="event-poll-stage" value="1" />
<div id="event-poll-date-times-table-wrapper"></div>
<div id="event-poll-stage3-wrapper">
<div id="event-poll-date-times-table-read-only-wrapper"></div>
<br />
<h2 id ="event-poll-title-choose-invitees"><?php echo elgg_echo('event_poll:choose_invitees:title'); ?></h2>
<?php echo elgg_view('input/userpicker');?>
<br />
<h2 id ="event-poll-title-message-to-invitees"><?php echo elgg_echo('event_poll:message_to_invitees:title'); ?></h2>
<label><?php echo elgg_echo('event_poll:subject:label')?></label> <?php echo elgg_view('input/text',array('name'=>'invitation_subject','value'=>$invitation_subject, 'id'=>'event-poll-invitation-subject')); ?>
<br />
<?php echo elgg_view('input/plaintext',array('name'=> 'invitation_body','value'=>$invitation_body)); ?>
</div>
<?php echo elgg_view('input/submit', array('id'=>'event-poll-back-button','name'=>'back','value'=>elgg_echo('event_poll:button:back')));?>
<?php echo elgg_view('input/submit', array('id'=>'event-poll-next2-button','name'=>'next2','value'=>elgg_echo('event_poll:button:next')));?>
<?php echo elgg_view('input/submit', array('id'=>'event-poll-back2-button','name'=>'back','value'=>elgg_echo('event_poll:button:back')));?>
<?php echo elgg_view('input/submit', array('id'=>'event-poll-send-button','name'=>'next2','value'=>elgg_echo('event_poll:button:send')));?>
<div class="event-poll-button-separator"></div>
<div class="event-poll-calendar-wrapper" id="calendar"></div>
<div id="event-poll-date-container">
<h3><?php echo elgg_echo('event_poll:selected_days'); ?></h3>
<div id="event-poll-date-wrapper"></div>
</div>
<div class="event-poll-button-separator"></div>
<?php echo elgg_view('input/submit', array('id'=>'event-poll-next-button','name'=>'next','value'=>elgg_echo('event_poll:button:next')));?>
