jQuery(document).ready(function($) {

	$('#events-full-calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		timeFormat: emCalendarArgs.timeFormat,
		columnFormat: {
			month: 'ddd',
			week: 'ddd, DD/MM',
			day: 'dddd'
		},
		firstDay: parseInt(emCalendarArgs.firstWeekDay),
		events: emCalendarArgs.events,
		editable: false,
		fixedWeekCount: false
    });
});