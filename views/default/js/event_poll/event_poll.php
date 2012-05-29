
//<script type="text/javascript">
elgg.provide('elgg.event_poll');

var event_poll_object = {};
var max_time_count = 0;
var click_id = 0;

elgg.event_poll.init = function () {
	$('#event-poll-next-button').click(elgg.event_poll.handleStage3);
	//$('#event-poll-next2-button').click(elgg.event_poll.handleStage3);
	$('#event-poll-back2-button').click(elgg.event_poll.handleStage1);
	$('#event-poll-edit-link').click(elgg.event_poll.handleStage1);
	$('#event-poll-back-button').click(elgg.event_poll.handleStage1);
	$('#event-poll-send-button').click(elgg.event_poll.sendPoll);
	$('.event-poll-date-option1-remove').live('click',elgg.event_poll.removeOption1);
	$('.event-poll-date-option2-remove').live('click',elgg.event_poll.removeOption2);
	$('#event-poll-vote-message').click(elgg.event_poll.handleVoteMessage);
	$('#event-poll-length-hour').change(elgg.event_poll.handleChangeLength);
	$('#event-poll-length-minute').change(elgg.event_poll.handleChangeLength);
	$('.event-poll-vote-checkbox').click(elgg.event_poll.handleVoteChoice);
	$('.event-poll-vote-none-checkbox').click(elgg.event_poll.handleVoteNoneChoice);
	$('[name="schedule_slot"][type=radio]').change(elgg.event_poll.handleTimeSelection);
	elgg.event_poll.handleTimeSelection();
	elgg.event_poll.setupCalendar();
}

elgg.event_poll.handleVoteChoice = function(e) {
	$('.event-poll-vote-none-checkbox').attr('checked', false);
}

elgg.event_poll.handleVoteNoneChoice = function(e) {
	$('.event-poll-vote-checkbox').attr('checked', false);
}

elgg.event_poll.handleTimeSelection = function() {
	if ($('[name="schedule_slot"][type=radio]:checked').val()) {
		$('#event-poll-vote-event-data-wrapper').show();
	}
}

elgg.event_poll.setupCalendar = function() {
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
		},
		defaultView: 'agendaWeek',
		allDaySlot: false,
		month: $('#event-poll-month-number').val(),
		firstHour: $('#event-poll-hour-number').val(),
		ignoreTimezone: true,
		editable: false,
		slotMinutes: 15,
		dayClick: elgg.event_poll.handleDayClick,
		eventClick : elgg.event_poll.handleEventClick,
		eventAfterRender : elgg.event_poll.handleEventRender, 
		events: elgg.event_poll.handleGetEvents
	});
}

elgg.event_poll.handleEventRender = function(event,element,view) {
	var guid = $('#event-poll-event-guid').val();
	if (event.guid == guid) {
		var click_id = event.click_id;
		element.find('.fc-event-content').append('<span rel="'+click_id+'" class="event-poll-delete-cell">[x]</span>');
		//element.after('<span rel="'+click_id+'" class="event-poll-delete-cell">[x]</span>');
	}
}

elgg.event_poll.deleteCell = function(e) {
	var click_id = $(this).attr('rel');
	alert(click_id);
	e.preventDefault();
	e.stopPropagation();
	e.stopImmediatePropagation();	
	return false;
}

elgg.event_poll.handleDayClick = function(date) {
	var stage = $('#event-poll-stage').val();
	if (stage == 1) {
	    var h = '<div class="event-poll-date-options">';
	    h += '<a class="event-poll-date-option1-remove" href="javascript:void(0);"><span class="elgg-icon elgg-icon-delete "></span></a>';
	    h += '<span class="event-poll-human-date">'+elgg.event_poll.formatDate(date)+'</span>';
	    h += '<span class="event-poll-iso-date">'+elgg.event_poll.getISODate(date)+'</span>';
	    h += '<span class="event-poll-click-id">'+click_id+'</span>';
	    h += '</div>';
	    $('#event-poll-date-container').addClass('event-poll-date-alert');
	    setTimeout(function() {$('#event-poll-date-container').removeClass('event-poll-date-alert');},250);
	    $('#event-poll-date-wrapper').append(h);

	    // add poll event to calendar
	    var guid = $('#event-poll-event-guid').val();
	    var lgth_m = parseInt($('#event-poll-length-minute').val());
		var lgth_h = parseInt($('#event-poll-length-hour').val());
	    var end_date = new Date(date.getTime()+1000*(lgth_h*60*60+lgth_m*60));
	    var event_item = {
			id: guid,
			guid: guid,
			click_id: click_id,
			title: $('#event-poll-event-title').val(),
			url: $('#event-poll-event-url').val(),
			start:  date.getTime()/1000,
			end : end_date.getTime()/1000,
			className: 'event-poll-new-class',
			allDay: false
	    };

	    $('#calendar').fullCalendar('renderEvent',event_item,true);
	    click_id += 1;
	}   
};

elgg.event_poll.handleEventClick = function(event,e) {
	if (e.target.className == 'event-poll-delete-cell') {
		click_id = e.target.getAttribute('rel');
		$('.event-poll-date-options').each(
			function(v) {
				var this_click_id = $(this).find('.event-poll-click-id').html();
				if (click_id == this_click_id) {
					$(this).remove();
				}
			}
		);
		$('#calendar').fullCalendar('removeEvents', function(event) { return event.click_id == click_id; });
		return false;
	} else {
	    if (event.url) {
	        $.fancybox({'href':event.url});
	        return false;
	    }
	}
};

elgg.event_poll.formatDate = function(date) {
	var d = $.datepicker.formatDate("MM d, yy", date);
	var h = date.getHours();
	var m = date.getMinutes();
	mf = m < 10 ? '0' + m : m;
	var lgth_m = $('#event-poll-length-minute').val();
	var lgth_h = $('#event-poll-length-hour').val();
	if($('#event-poll-time-format').val() == 12) {
		if (h == 0) {
			t = "12:"+mf+" am";
		} else if (h == 12) {
			t = "12:"+mf+" pm";
		} else if (h < 12) {
			t = h+":"+mf+" am";
		} else {
			t = (h-12)+":"+mf+" pm";
		}		
		
	} else {
		t = h+":"+m;
	}
	var r = '';
	date2 = new Date(date.getTime());
	if(lgth_m) {
		date2.setHours(h + parseInt(lgth_h));
		date2.setMinutes(m + parseInt(lgth_m));
		var d2 = $.datepicker.formatDate("MM d, yy", date2);
		var h2 = date2.getHours();
		var m2 = date2.getMinutes();
		mf2 = m2 < 10 ? '0' + m2 : m2;
		if($('#event-poll-time-format').val() == 12) {
			if (h2 == 0) {
				t2 = "12:"+mf2+" am";
			} else if (h2 == 12) {
				t2 = "12:"+mf2+" pm";
			} else if (h2 < 12) {
				t2 = h2+":"+mf2+" am";
			} else {
				t2 = (h2-12)+":"+mf2+" pm";
			}			
		} else {
			t2 = h2+":"+mf2;
		}
		if (d == d2) {
			r = d+" ("+t+" - "+t2+')';
		} else {
			r = d+" ("+t+") - "+d2+" ("+t2+")";
		}
	} else {
		r = d+" ("+t+")";
	}
	r += '<span class="event-poll-human-date-bit">'+d+'</span><span class="event-poll-human-time-bit">'+t+'</span>';
	return r;
}

elgg.event_poll.doNothing = function(e,jsEvent) {
	elgg.event_poll.handleDayClick(e.start);
	jsEvent.preventDefault();
	jsEvent.stopPropagation();
	jsEvent.stopImmediatePropagation();	 
}

elgg.event_poll.getISODate = function(d) {
	var year = d.getFullYear();
	var month = d.getMonth()+1;
	month =	month < 10 ? '0' + month : month;
	var day = d.getDate();
	day = day < 10 ? '0' + day : day;
	var h = d.getHours();
	var m = d.getMinutes();
	m =	m < 10 ? '0' + m : m;
	return h+":"+m+" "+year +"-"+month+"-"+day;
}

elgg.event_poll.formatTime = function(d) {
	var hours = d.getHours();
	var minutes = d.getMinutes();
	minutes = minutes < 10 ? '0' + minutes : minutes;
	return hours+":"+minutes;
}

elgg.event_poll.handleGetEvents = function(start, end, callback) {	
	var start_date = elgg.event_poll.getISODate(start);
	var end_date = elgg.event_poll.getISODate(end);
	var url = "event_calendar/get_fullcalendar_events/"+start_date+"/"+end_date+"/all/<?php echo $vars['group_guid']; ?>";
	elgg.getJSON(url, {success: 
		function(events) {
			var guid = $('#event-poll-event-guid').val();
			$.each(events,function(k,e) {if (e.guid == guid) { e.className = 'event-poll-new-class'; }});
			callback(events);
		}
	});
}

/*elgg.event_poll.createEventObject = function() {
	event_poll_object = {};
	$('.event-poll-date-times-table-row').each(
		function() {
			var iso = $(this).find('.event-poll-iso-date').html();
			var human = $(this).find('.event-poll-human-date').html();
			event_poll_object[iso] = {};
			event_poll_object[iso]['human'] = human;
			var selects = $(this).find('[name="event_poll_time"]').get();
			event_poll_object[iso]['times'] = [];
			event_poll_object[iso]['human_times'] = [];
			for (var i = 0; i < selects.length; i++) {
				event_poll_object[iso]['times'].push(selects[i].value);
				var index = selects[i].selectedIndex;
				if (index == -1) {
					event_poll_object[iso]['human_times'].push('-');
				} else {
					event_poll_object[iso]['human_times'].push(selects[i].options[index].text);
				}
			}
		}
	);
}*/

// TODO - rework this for new 2 page system
elgg.event_poll.createEventObject = function() {
	max_time_count = 0;
	event_poll_object = {};
	$('.event-poll-date-options').each(
		function() {
			var iso = $(this).find('.event-poll-iso-date').html();
			var human_time = $(this).find('.event-poll-human-time-bit').html();
			var human_date = $(this).find('.event-poll-human-date-bit').html();
			var td_bits = iso.split(" ");
			var t_bits = td_bits[0].split(":");
			var h = parseInt(t_bits[0]);
			var m = parseInt(t_bits[1]);
			var t = h*60+m;
			var d = td_bits[1];
			if (!(d in event_poll_object)) {
				event_poll_object[d] = {};
				event_poll_object[d]['human_date'] = human_date;
				event_poll_object[d]['times'] = [];
				event_poll_object[d]['human_times'] = [];
			}
			event_poll_object[d]['times'].push(t);
			event_poll_object[d]['human_times'].push(human_time);
			if (max_time_count < event_poll_object[d]['times'].length) {
				max_time_count = event_poll_object[d]['times'].length;
			}
		}
	);
}

elgg.event_poll.handleStage2 = function(e) {
	// remove the existing table, if any
	$('#event-poll-date-times-table').remove();
	// set up the new table
	var tb = '<table id="event-poll-date-times-table"><tr>';
	tb += '<th class="event-poll-date-times-table-date">&nbsp;</th>';
	// TODO - make the number of time slots configurable
	tb += '<th class="event-poll-date-times-table-time">Time 1</th>';
	tb += '<th class="event-poll-date-times-table-time">Time 2</th>';
	tb += '<th class="event-poll-date-times-table-time">Time 3</th>';
	tb += '</tr></table>';
	// insert the new table
	$('#event-poll-date-times-table-wrapper').prepend(tb);
	// add the data rows
	$('.event-poll-date-options').each(elgg.event_poll.insertTableRow);
	// get the times dropdown and populate the table with it
    elgg.get('event_poll/get_times_dropdown', {success: elgg.event_poll.populateTimesDropdowns});
    $('#event-poll-next-button').hide();
    $('#event-poll-next2-button').show();
    $('#event-poll-back-button').show();
    $('#event-poll-back2-button').hide();
    $('#event-poll-send-button').hide();
    $('#event-poll-title1').hide();
    $('#event-poll-title2').show();
    $('#event-poll-title3').hide();
    $('#event-poll-stage').val(2);
    $('#event-poll-date-container').hide();
    $('#event-poll-date-times-table-wrapper').show();
    $('#event-poll-date-times-table-read-only-wrapper').hide();
    $('#event-poll-stage3-wrapper').hide();
 	// show calendar
    $('#calendar').show();
	e.preventDefault();
}

elgg.event_poll.handleStage3 = function(e) {
	elgg.event_poll.createEventObject();
	var event_length = 60*parseInt($('#event-poll-length-hour').val()) + parseInt($('#event-poll-length-minute').val());
	elgg.action('event_poll/set_poll',{data : {poll: event_poll_object, event_length:event_length, guid: $('#event-poll-event-guid').val()}});
	elgg.event_poll.populateReadOnlyTable(max_time_count);

	$('#event-poll-date-container').hide();
	$('#event-poll-stage1-wrapper').hide();
	$('#event-poll-send-button').hide();

	// show read-only table	
	$('#event-poll-date-times-table-read-only-wrapper').show();
    $('#event-poll-date-times-table-wrapper').hide();

    // hide calendar
    $('#calendar').hide();

    // show title
	$('#event-poll-title1').hide();
    $('#event-poll-title2').hide();
    $('#event-poll-title3').show();

    // show invitation form
    $('#event-poll-stage3-wrapper').show();

    // show buttons
    $('#event-poll-next-button').hide();
    $('#event-poll-back-button').hide();
    $('#event-poll-back2-button').show();
    $('#event-poll-send-button').show();

    // set stage
	$('#event-poll-stage').val(3);
	
	e.preventDefault();
}

elgg.event_poll.populateReadOnlyTable = function() {
	$('#event-poll-readonly-table').remove();
	// set up the new table
	var tb = '<table id="event-poll-readonly-table"><tr>';
	tb += '<th class="event-poll-date-times-table-date">&nbsp;</th>';
	// TODO - make the number of time slots configurable
	for (var i=1; i <= max_time_count; i++) {
		tb += '<th class="event-poll-date-times-table-time">Time '+i+'</th>';
	}

	tb += '</tr></table>';
	// insert the new table
	$('#event-poll-date-times-table-read-only-wrapper').prepend(tb);
	// add the data rows
	$.each(event_poll_object,elgg.event_poll.insertReadOnlyTableRow);
}

elgg.event_poll.insertReadOnlyTableRow = function(index,item) {
	human = item['human_date'];
	times = item['human_times'];
	var t = '<tr class="event-poll-readonly-table-row">';
    t += '<td>';
    t += '<span class="event-poll-human-date">'+human+'</span>';
    t += '</td>';
    $.each(times,
    	function (index,time) {
    		t += '<td class="event-poll-time-readonly">'+time+'</td>';
    	}
    );
    t += '</tr>';
    $('#event-poll-readonly-table').append(t);
}

elgg.event_poll.handleStage1 = function(e) {
	// TODO - put this next bit elsewhere when really editing an event poll
	// add the data rows
	//$.each(event_poll_object,elgg.event_poll.insertDateDiv);
	$('#event-poll-back2-button').hide();
    $('#event-poll-send-button').hide();
	$('#event-poll-next-button').show();
    $('#event-poll-next2-button').hide();
    $('#event-poll-back-button').hide();
    $('#event-poll-title1').show();
    $('#event-poll-title2').hide();
    $('#event-poll-title3').hide();
    $('#event-poll-stage').val(1);
    $('#event-poll-date-container').show();
    $('#calendar').show();
    $('#event-poll-date-times-table-wrapper').hide();
    $('#event-poll-stage3-wrapper').hide();
    $('#event-poll-stage1-wrapper').show();
	e.preventDefault();
}

elgg.event_poll.handleChangeLength = function(e) {
	elgg.event_poll.createEventObject();
	$('#event-poll-date-wrapper').remove();
	$('#event-poll-date-container').append('<div id="event-poll-date-wrapper"></div>');
	$.each(event_poll_object,elgg.event_poll.insertDateDiv);
}

elgg.event_poll.insertTableRow = function(index) {
	var human = $(this).find('.event-poll-human-date').html();
	var iso = $(this).find('.event-poll-iso-date').html();
	var t = '<tr class="event-poll-date-times-table-row">';
    t += '<td>';
	t += '<a class="event-poll-date-option2-remove" href="javascript:void(0);"><span class="elgg-icon elgg-icon-delete "></span></a>';
    t += '<span class="event-poll-human-date">'+human+'</span><span class="event-poll-iso-date">'+iso+'</span></td>';
    t += '<td class="event-poll-times-dropdown"></td><td class="event-poll-times-dropdown"></td><td class="event-poll-times-dropdown"></td>';
    t += '</tr>';
    $('#event-poll-date-times-table').append(t);
}

elgg.event_poll.insertDateDiv = function(key,value) {
	var date_bits = key.split('-');
	var date = new Date(parseInt(date_bits[0]),parseInt(date_bits[1])-1,parseInt(date_bits[2]));
	var t = date.getTime();
	$.each(value['times'], function(k,v) {
		var nd = new Date(t);
		nd.setMinutes(v);
		elgg.event_poll.handleDayClick(nd);
	});
}

elgg.event_poll.setSelectValuesForRow = function() {
	var iso = $(this).find('.event-poll-iso-date').html();
	if (event_poll_object[iso] != undefined) {
		$($(this).find('[name="event_poll_time"]')).each(
			function (index) {
				var time = event_poll_object[iso]['times'][index];
				$(this).val(time);				
			}
		);
	}
}

elgg.event_poll.populateTimesDropdowns = function(data) {
	$('.event-poll-times-dropdown').append(data);
	$('.event-poll-date-times-table-row').each(elgg.event_poll.setSelectValuesForRow);
}

elgg.event_poll.removeOption1 = function(e) {
	var p = $(this).parent();
	var iso = p.find('.event-poll-iso-date').html();
	var click_id = p.find('.event-poll-click-id').html();
	$('#calendar').fullCalendar('removeEvents', function(e) { return e.click_id == click_id; });
	$(this).parent().remove();
	delete event_poll_object[iso];
}

elgg.event_poll.removeOption2 = function(e) {
	var iso = $(this).find('.event-poll-iso-date').html();
	$(this).parent().parent().remove();
	delete event_poll_object[iso];
}

elgg.event_poll.sendPoll = function(e) {
	d = {	guid : $('#event-poll-event-guid').val(), 
			subject : $('[name="invitation_subject"]').val(),
			body : $('[name="invitation_body"]').val(),
			invitees : $('input[name="members[]"]').map(function(){return $(this).val();}).get()	
	};
	elgg.action('event_poll/invite', {data: d, success: function(response) {alert(response['output'].msg);}});
	elgg.forward('event_poll/list/all');
	//$('input[name="members[]"]').parent().remove();
	e.preventDefault();
}

elgg.event_poll.handleVoteMessage = function(e) {
	var m = elgg.echo('event_poll:vote_message:explanation');
	if ($(this).html() == m) {
		$(this).html('');
	}
}

elgg.register_hook_handler('init', 'system', elgg.event_poll.init);
//</script>