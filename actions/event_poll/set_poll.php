<?php
$guid = get_input('guid');
$event_length = get_input('event_length');
$poll = get_input('poll');

elgg_load_library('elgg:event_poll');
echo elgg_poll_set_poll($guid,$poll,$event_length);
