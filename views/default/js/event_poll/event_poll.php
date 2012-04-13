//<script type="text/javascript">
elgg.provide('elgg.event_poll');

var event_poll_object = {};

elgg.event_poll.init = function () {
	$('#event-poll-next-button').click(elgg.event_poll.handleStage2);
	$('#event-poll-next2-button').click(elgg.event_poll.handleStage3);
	$('#event-poll-back2-button').click(elgg.event_poll.handleStage2);
	$('#event-poll-edit-link').click(elgg.event_poll.handleStage2);
	$('#event-poll-back-button').click(elgg.event_poll.handleStage1);
	$('#event-poll-send-button').click(elgg.event_poll.sendPoll);
	$('.event-poll-date-option1-remove').live('click',elgg.event_poll.removeOption1);
	$('.event-poll-date-option2-remove').live('click',elgg.event_poll.removeOption2);
	$('#event-poll-vote-message').click(elgg.event_poll.handleVoteMessage);
	elgg.event_poll.setupCalendar();
}

elgg.event_poll.setupCalendar = function() {
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek'
		},
		month: $('#event-poll-month-number').val(),
		ignoreTimezone: true,
		editable: false,
		slotMinutes: 15,
		dayClick: elgg.event_poll.handleDayClick,
		eventClick : elgg.event_poll.doNothing,
		events: elgg.event_poll.handleGetEvents
	});
}

elgg.event_poll.handleDayClick = function(date) {
	var stage = $('#event-poll-stage').val();
	if (stage == 1) {
	    var h = '<div class="event-poll-date-options">';
	    h += '<a class="event-poll-date-option1-remove" href="javascript:void(0);"><span class="elgg-icon elgg-icon-delete "></span></a>';
	    h += '<span class="event-poll-human-date">'+elgg.event_poll.formatDate(date)+'</span>';
	    h += '<span class="event-poll-iso-date">'+elgg.event_poll.getISODate(date)+'</span>';
	    h += '</div>';
	    $('#event-poll-date-wrapper').append(h);
	}   
};

elgg.event_poll.formatDate = function(date) {
	return $.datepicker.formatDate("DD, MM d, yy", date);
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
	return year +"-"+month+"-"+day;
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
			callback(events);
		}
	});
}

elgg.event_poll.createEventObject = function() {
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
	elgg.action('event_poll/set_poll',{data : {poll: event_poll_object, guid: $('#event-poll-event-guid').val()}});
	elgg.event_poll.populateReadOnlyTable();

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
    $('#event-poll-next2-button').hide();
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
	tb += '<th class="event-poll-date-times-table-time">Time 1</th>';
	tb += '<th class="event-poll-date-times-table-time">Time 2</th>';
	tb += '<th class="event-poll-date-times-table-time">Time 3</th>';
	tb += '</tr></table>';
	// insert the new table
	$('#event-poll-date-times-table-read-only-wrapper').prepend(tb);
	// add the data rows
	$.each(event_poll_object,elgg.event_poll.insertReadOnlyTableRow);
}

elgg.event_poll.insertReadOnlyTableRow = function(index,item) {
	human = item['human'];
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
	elgg.event_poll.createEventObject();
	// remove the existing wrapper div and contents
	$('#event-poll-date-wrapper').remove();
	// set up the new div
	var div = '<div id="event-poll-date-wrapper"></div>';
	// insert the new div
	$('#event-poll-date-container').append(div);
	// add the data rows
	$('.event-poll-date-times-table-row').each(elgg.event_poll.insertDateDiv);
	$('#event-poll-next-button').show();
    $('#event-poll-next2-button').hide();
    $('#event-poll-back-button').hide();
    $('#event-poll-title1').show();
    $('#event-poll-title2').hide();
    $('#event-poll-title3').hide();
    $('#event-poll-stage').val(1);
    $('#event-poll-date-container').show();
    $('#event-poll-date-times-table-wrapper').hide();
    $('#event-poll-stage3-wrapper').hide();
	e.preventDefault();
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

elgg.event_poll.insertDateDiv = function(index) {
	var human = $(this).find('.event-poll-human-date').html();
	var iso = $(this).find('.event-poll-iso-date').html();
	var h = '<div class="event-poll-date-options">';
    h += '<a class="event-poll-date-option1-remove" href="javascript:void(0);"><span class="elgg-icon elgg-icon-delete "></span></a>';
    h += '<span class="event-poll-human-date">'+human+'</span>';
    h += '<span class="event-poll-iso-date">'+iso+'</span>';
    h += '</div>';
    $('#event-poll-date-wrapper').append(h);
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
	var iso = $(this).find('.event-poll-iso-date').html();
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
	$('input[name="members[]"]').parent().remove();
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