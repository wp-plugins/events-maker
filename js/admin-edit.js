( function ( $ ) {

	$( document ).ready( function () {

		$( '.events-datepicker' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			firstDay: emEditArgs.firstWeekDay,
			showButtonPanel: false,
			monthNames: emEditArgs.monthNames,
			monthNamesShort: emEditArgs.monthNamesShort,
			dayNames: emEditArgs.dayNames,
			dayNamesShort: emEditArgs.dayNamesShort,
			dayNamesMin: emEditArgs.dayNamesMin,
			isRTL: emEditArgs.isRTL
		} );

		$( '.toggle-featured-event' ).on( 'click', function ( e ) {
			e.preventDefault();

			var _el = $( 'span', this );
			var post_id = $( this ).attr( 'data-post-id' );
			var data = {
				action: 'events_maker_feature_event',
				event_id: post_id,
				em_nonce: emEditArgs.nonce
			};

			$.ajax( {
				url: ajaxurl,
				data: data,
				type: 'post',
				dataType: 'json',
				success: function ( data ) {
					_el.removeClass( 'dashicons-star-filled' ).removeClass( 'dashicons-star-empty' );
					if ( data.featured == true ) {
						_el.addClass( 'dashicons-star-filled' );
					} else {
						_el.addClass( 'dashicons-star-empty' );
					}
				}

			} );
		} );

	} );

} )( jQuery );