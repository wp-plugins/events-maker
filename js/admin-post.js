jQuery(document).ready(function($) {

	var emTicketNo = $('#event_tickets').attr('rel');

	$('#event_start_date, #event_end_date').datepicker({
		dateFormat: 'yy-mm-dd',
		firstDay: emPostArgs.firstWeekDay,
		showButtonPanel: false,
		monthNames: emPostArgs.monthNames,
		monthNamesShort: emPostArgs.monthNamesShort,
		dayNames: emPostArgs.dayNames,
		dayNamesShort: emPostArgs.dayNamesShort,
		dayNamesMin: emPostArgs.dayNamesMin,
		isRTL: emPostArgs.isRTL
	});

	$('#event_start_time, #event_end_time').timepicker({
		timeFormat: 'HH:mm',
		stepMinute: 5,
		timeOnly: true
	});

	$(document).on('click', '#event_add_ticket', function(event) {
		var ticketsHtml = '';

		emTicketNo++;

		for(i in emPostArgs.ticketsFields) {
			ticketsHtml += ' <label for="event_tickets['+emTicketNo+']['+i+']">'+emPostArgs.ticketsFields[i]+':</label> <input type="text" id="event_tickets['+emTicketNo+']['+i+']" name="event_tickets['+emTicketNo+']['+i+']" value="" />'+(i === 'price' ? emPostArgs.currencySymbol : '');
		}

		$('#event_tickets').fadeIn(300).append('<p style="display: none;">'+ticketsHtml+' <a href="#" class="event_ticket_delete button button-secondary">'+emPostArgs.ticketDelete+'</a></p>');
		$('#event_tickets p:last').fadeIn(300);

		return false;
	});

	$(document).on('click', '.event_ticket_delete', function(event) {
		if(confirm(emPostArgs.deleteTicket)) {
			$(this).parent().fadeOut(300, function() {
				$(this).remove();
			});
		}

		return false;
	});

	$(document).on('change', 'input#event_free', function(event) {
		if($('#event_free:checked').val() === 'on') {
			$('#event_cost_and_tickets').fadeOut(300);
		} else {
			$('#event_cost_and_tickets').fadeIn(300);
		}
	});

	$(document).on('change', 'input#event_all_day', function(event) {
		if($('#event_all_day:checked').val() === 'on') {
			$('#event_start_time, #event_end_time').fadeOut(300);
		} else {
			$('#event_start_time, #event_end_time').fadeIn(300);
		}
	});
});