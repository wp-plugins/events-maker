jQuery(document).ready(function($) {

	$('.wplikebtns').buttonset();

	$(document).on('change', '#em-event-nav-menu-yes, #em-event-nav-menu-no', function() {
		if($('#em-event-nav-menu-yes:checked').val() === 'yes') {
			$('#em_event_nav_menu_opt').fadeIn(300);
		} else if($('#em-event-nav-menu-no:checked').val() === 'no') {
			$('#em_event_nav_menu_opt').fadeOut(300);
		}
	});

	$(document).on('click', 'input#reset_em_general, input#reset_em_templates, input#reset_em_capabilities, input#reset_em_permalinks', function() {
		return confirm(emArgs.resetToDefaults);
	});
});