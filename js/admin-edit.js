jQuery(document).ready(function($) {

	$('.events-datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		firstDay: emEditArgs.firstWeekDay,
		showButtonPanel: false,
		monthNames: emEditArgs.monthNames,
		monthNamesShort: emEditArgs.monthNamesShort,
		dayNames: emEditArgs.dayNames,
		dayNamesShort: emEditArgs.dayNamesShort,
		dayNamesMin: emEditArgs.dayNamesMin,
		isRTL: emEditArgs.isRTL
	});
});