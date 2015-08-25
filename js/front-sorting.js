( function ( $ ) {

	$( document ).ready( function () {

		// Orderby
		$( '.events-maker-orderby' ).on( 'change', 'select.orderby', function () {
			$( this ).closest( 'form' ).submit();
		} );

	} );

} )( jQuery );