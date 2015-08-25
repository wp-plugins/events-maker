( function ( $ ) {

	$( document ).ready( function () {

		var args = $.parseJSON( emCalendarArgs );

		$( '#events-full-calendar' ).fullCalendar( args );

	} );

} )( jQuery );