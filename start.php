<?php
elgg_register_event_handler('init','system','event_poll_init');
function event_poll_init() {
	
	elgg_register_library('elgg:event_poll', elgg_get_plugins_path() . 'event_poll/models/model.php');
	
	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('event_poll','event_poll_page_handler');
	
	// register the plugin's JavaScript
	$plugin_js = elgg_get_simplecache_url('js', 'event_poll/event_poll');
	elgg_register_js('elgg.event_poll', $plugin_js);
	
	//add to the css
	elgg_extend_view('css/elgg', 'event_poll/css');
	
	// register actions
	$action_path = elgg_get_plugins_path() . 'event_poll/actions/event_poll';
	elgg_register_action("event_poll/edit","$action_path/edit.php");
	elgg_register_action("event_poll/set_poll","$action_path/set_poll.php");
	elgg_register_action("event_poll/invite","$action_path/invite.php");
	elgg_register_action("event_poll/vote","$action_path/vote.php");
	elgg_register_action("event_poll/schedule","$action_path/schedule.php");
	elgg_register_action("event_poll/schedule_message","$action_path/schedule_message.php");
}

/**
 * Dispatches event poll pages.
 *
 * URLs take the form of
 *  New event poll:        			event_poll/add/<event_guid>
 *  Edit event poll:       			event_poll/edit/<event_guid>
 *  Vote in poll:  					event_poll/vote/<event_guid>
 *  Schedule event:  				event_poll/schedule/<event_guid>
 *
 * @param array $page
 * @return NULL
 */
function event_poll_page_handler($page) {

	elgg_load_library('elgg:event_poll');
	$page_type = $page[0];
	switch ($page_type) {		
		case 'add':
		case 'edit':
			gatekeeper();
			echo event_poll_get_page_content_edit($page_type,$page[1]);
			break;
		case 'vote':
			gatekeeper();
			echo event_poll_get_page_content_vote($page[1]);
			break;
		case 'schedule':
			gatekeeper();
			echo event_poll_get_page_content_schedule($page[1]);
			break;
		case 'get_times_dropdown':
			gatekeeper();
			echo event_poll_get_times_dropdown();
			break;
		default:
			return FALSE;
	}
	return TRUE;
}