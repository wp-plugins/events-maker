jQuery(document).ready(function($) {

	// whether to display navigation menu settings
	$(document).on('change', '#em-event-nav-menu-checkbox', function() {
		if($(this).is(':checked')) {
			$('#em_event_nav_menu_opt').fadeIn(300);
		} else {
			$('#em_event_nav_menu_opt').fadeOut(300);
		}
	});


	// whether to restore settings to defaults
	$(document).on('click', 'input#reset_em_general, input#reset_em_display, input#reset_em_templates, input#reset_em_capabilities, input#reset_em_permalinks', function() {
		return confirm(emArgs.resetToDefaults);
	});


	// whether to display page settings
	$(document).on('change', 'input[name="events_maker_general[full_calendar_display][type]"]', function() {
		if($(this).val() === 'page') {
			$('#event-full-calendar-display-page').fadeIn(300);
		} else {
			$('#event-full-calendar-display-page').fadeOut(300);
		}
	});
});