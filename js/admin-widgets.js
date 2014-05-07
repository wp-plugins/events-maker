jQuery(document).ready(function($) {

	$(document).on('change', '.taxonomy-select-cats', function() {
		if($(this).val() === 'selected') {
			$(this).parent().find('.checkbox-list-cats').fadeIn(300);
		} else if($(this).val() === 'all') {
			$(this).parent().find('.checkbox-list-cats').fadeOut(300);
		}
	});

	$(document).on('change', '.taxonomy-select-locs', function() {
		if($(this).val() === 'selected') {
			$(this).parent().find('.checkbox-list-locs').fadeIn(300);
		} else if($(this).val() === 'all') {
			$(this).parent().find('.checkbox-list-locs').fadeOut(300);
		}
	});

	$(document).on('change', '.taxonomy-select-orgs', function() {
		if($(this).val() === 'selected') {
			$(this).parent().find('.checkbox-list-orgs').fadeIn(300);
		} else if($(this).val() === 'all') {
			$(this).parent().find('.checkbox-list-orgs').fadeOut(300);
		}
	});

	$(document).on('change', '.em-show-event-thumbnail', function() {
		if($(this).is(':checked')) {
			$(this).parent().parent().find('.em-event-thumbnail-size').fadeIn(300);
		} else {
			$(this).parent().parent().find('.em-event-thumbnail-size').fadeOut(300);
		}
	});
});