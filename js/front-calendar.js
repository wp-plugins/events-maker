jQuery(document).ready(function($) {

	$('#events-full-calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		timeFormat: 'H:mm',
		columnFormat: {
			month: 'ddd',
			week: 'ddd, DD/MM',
			day: 'dddd'
		},
		firstDay: emCalendarArgs.firstWeekDay,
		events: emCalendarArgs.events,
		editable: false
    });
});