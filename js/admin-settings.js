( function ( $ ) {

	$( document ).ready( function () {

		// whether to display navigation menu settings
		$( '#em-event-nav-menu-checkbox' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '#em_event_nav_menu_opt' ).fadeIn( 300 );
			} else {
				$( '#em_event_nav_menu_opt' ).fadeOut( 300 );
			}
		} );

		// whether to restore settings to defaults
		$( 'input#reset_em_general, input#reset_em_display, input#reset_em_templates, input#reset_em_capabilities, input#reset_em_permalinks' ).on( 'click', function () {
			return confirm( emArgs.resetToDefaults );
		} );

		// display 'create page' button if needed
		$( '.action-page select' ).on( 'change', function () {
			if ( $( this ).val() === '0' ) {
				$( this ).parent().find( '.create-page' ).fadeIn( 300 );
			} else {
				$( this ).parent().find( '.create-page' ).fadeOut( 300 );
			}
		} );

	} );

} )( jQuery );