jQuery(document).ready(function($) {
	
	// Orderby
	$('.events-maker-ordering').on('change', 'select.orderby', function() {
		$(this).closest('form').submit();
	});
	
});