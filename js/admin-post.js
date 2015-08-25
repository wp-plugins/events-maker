( function ( $ ) {

	$( document ).ready( function () {

		var emTicketNo = $( '#event_tickets p:last' ).attr( 'rel' ),
				startDate = new Date( $( '#event_start_date' ).val() ),
				customEventsCount = $( '.event-custom' ).length,
				customOccurrences = $( '#event_custom_occurrences' ).children( '.event-custom' ),
				dateOptions = {
					dateFormat: 'yy-mm-dd',
					firstDay: emPostArgs.firstWeekDay,
					showButtonPanel: false,
					monthNames: emPostArgs.monthNames,
					monthNamesShort: emPostArgs.monthNamesShort,
					dayNames: emPostArgs.dayNames,
					dayNamesShort: emPostArgs.dayNamesShort,
					dayNamesMin: emPostArgs.dayNamesMin,
					isRTL: emPostArgs.isRTL
				},
		timeOptions = {
			timeFormat: 'HH:mm',
			stepMinute: 5,
			timeOnly: true
		};


		// datepicker
		$( '#event_start_date, #event_end_date' ).datepicker( dateOptions );
		$( '#event_end_date' ).datepicker( 'option', 'minDate', startDate );


		// until datepicker
		$( '.event_recurrence_until' ).datepicker( dateOptions );
		$( '.event_recurrence_until' ).datepicker( 'option', 'minDate', startDate );


		// custom occurrences datepicker and timepicker
		customOccurrences.find( '.event_custom_time' ).timepicker( timeOptions );
		customOccurrences.find( '.event_custom_date' ).datepicker( dateOptions );
		customOccurrences.find( '.start .event_custom_date' ).datepicker( 'option', 'minDate', startDate );
		customOccurrences.find( '.end .event_custom_date' ).datepicker( 'option', 'minDate', new Date( $( '#event_end_date' ).val() ) );


		// timepicker
		$( '#event_start_time, #event_end_time' ).timepicker( timeOptions );


		// event options
		$( '#event-options .edit-event-options' ).click( function () {
			if ( $( '#event-options-list' ).is( ":hidden" ) ) {
				$( '#event-options-list' ).slideDown( 'fast' );
				$( this ).hide();
			}
			return false;
		} );

		$( '#event-options .save-event-options' ).click( function () {
			$( '#event-options-list' ).slideUp( 'fast' );
			$( '#event-options .edit-event-options' ).show();

			var label = ', ' + $.trim( $( '#event-options-shortlist strong' ).text() );
			var options = $( "label[for*='event_display_option']" );

			$( options ).each( function ( index, value ) {
				if ( $( this ).find( 'input' ).is( ':checked' ) ) {
					if ( label.indexOf( $.trim( $( this ).text() ) ) < 2 ) {
						if ( label.length > 2 ) {
							label = label + ', ' + $.trim( $( this ).text() );
						} else {
							label = label + $.trim( $( this ).text() );
						}
					}
				} else {
					if ( label.indexOf( $.trim( $( this ).text() ) ) >= 2 ) {
						label = label.replace( ', ' + $.trim( $( this ).text() ), '' );
					}
				}
			} );

			if ( $( 'input[name=event_featured]' ).is( ':checked' ) ) {
				// if string does not exist, add it
				if ( label.indexOf( $.trim( $( 'label[for=event_featured]' ).text() ) ) < 2 ) {
					if ( label.length > 2 ) {
						label = label + ', ' + $.trim( $( 'label[for=event_featured]' ).text() );
					} else {
						label = label + $.trim( $( 'label[for=event_featured]' ).text() );
					}
				}
				$( 'input[name=event_featured]' ).attr( 'checked', 'checked' );
			} else {
				// if string exists, remove it
				if ( label.indexOf( $.trim( $( 'label[for=event_featured]' ).text() ) ) >= 2 ) {
					label = label.replace( ', ' + $.trim( $( 'label[for=event_featured]' ).text() ), '' );
				}
			}

			// remove first comma
			label = label.replace( ', ', '' );

			$( '#event-options-shortlist strong' ).text( label );
			return false;
		} );

		$( '#event-options .cancel-event-options' ).click( function () {
			$( '#event-options-list' ).slideUp( 'fast' );
			$( '#event-options .edit-event-options' ).show();

			var label = $.trim( $( '#event-options-shortlist strong' ).text() );
			if ( label.length > 0 ) {
				label = ', ' + label;
			}
			var current_featured = $( '#current_featured' ).val();
			var current_options = $.parseJSON( $( '#current_options' ).val() );

			$.each( current_options, function ( index, value ) {
				if ( value == true ) {
					// if string does not exist, add it
					if ( label.indexOf( $.trim( $( 'label[for=event_display_option_' + index + ']' ).text() ) ) < 2 ) {
						label = label + ', ' + $.trim( $( 'label[for=event_display_option_' + index + ']' ).text() );
					}
					$( 'input#event_display_option_' + index ).attr( 'checked', 'checked' );
				} else {
					// if string exists, remove it
					if ( label.indexOf( $.trim( $( 'label[for=event_display_option_' + index + ']' ).text() ) ) >= 2 ) {
						label = label.replace( ', ' + $.trim( $( 'label[for=event_display_option_' + index + ']' ).text() ), '' );
					}
					$( 'input#event_display_option_' + index ).removeAttr( 'checked' );
				}
			} );

			if ( current_featured == '1' ) {
				// if string does not exist, add it
				if ( label.indexOf( $.trim( $( 'label[for=event_featured]' ).text() ) ) < 2 ) {
					label = label + ', ' + $.trim( $( 'label[for=event_featured]' ).text() );
				}
				$( 'input[name=event_featured]' ).attr( 'checked', 'checked' );
			} else {
				// if string exists, remove it
				if ( label.indexOf( $.trim( $( 'label[for=event_featured]' ).text() ) ) >= 2 ) {
					label = label.replace( ', ' + $.trim( $( 'label[for=event_featured]' ).text() ), '' );
				}
				$( 'input[name=event_featured]' ).removeAttr( 'checked' );
			}

			// remove first comma
			label = label.replace( ', ', '' );

			$( '#event-options-shortlist strong' ).text( label );
			return false;
		} );



		// adds new ticket
		$( document ).on( 'click', '#event_add_ticket', function ( event ) {
			var ticketsHtml = '';

			emTicketNo++;

			for ( i in emPostArgs.ticketsFields ) {
				ticketsHtml += ' <label for="event_tickets[' + emTicketNo + '][' + i + ']">' + emPostArgs.ticketsFields[i] + ':</label> <input type="text" id="event_tickets[' + emTicketNo + '][' + i + ']" name="event_tickets[' + emTicketNo + '][' + i + ']" value="" />' + ( i === 'price' ? emPostArgs.currencySymbol : '' );
			}

			$( '#event_tickets' ).fadeIn( 300 ).append( '<p style="display: none;" rel="' + emTicketNo + '">' + ticketsHtml + ' <a href="#" class="event_ticket_delete button button-secondary">' + emPostArgs.ticketDelete + '</a></p>' );
			$( '#event_tickets p:last' ).fadeIn( 300 );

			return false;
		} );


		// deletes ticket
		$( document ).on( 'click', '.event_ticket_delete', function () {
			if ( confirm( emPostArgs.deleteTicket ) ) {
				$( this ).parent().fadeOut( 300, function () {
					$( this ).remove();
				} );
			}

			return false;
		} );


		// prevents putting date before event starts
		$( document ).on( 'change', '#event_start_date', function () {
			var start = new Date( $( this ).val() );

			$( '.event-custom .start .event_custom_date' ).datepicker( 'option', 'minDate', start );
			$( '.event_recurrence_until' ).datepicker( 'option', 'minDate', start );
			$( '#event_end_date' ).datepicker( 'option', 'minDate', start );
		} );


		// prevents putting date before event ends
		$( document ).on( 'change', '#event_end_date', function () {
			$( '.event-custom .end .event_custom_date' ).datepicker( 'option', 'minDate', new Date( $( this ).val() ) );
		} );


		// is it free event?
		$( document ).on( 'change', 'input#event_free', function () {
			if ( $( '#event_free:checked' ).val() === 'on' ) {
				$( '#event_cost_and_tickets' ).fadeOut( 300 );
			} else {
				$( '#event_cost_and_tickets' ).fadeIn( 300 );
			}
		} );


		// is it all day event?
		$( document ).on( 'change', 'input#event_all_day', function () {
			if ( $( '#event_all_day:checked' ).val() === 'on' ) {
				$( '#event_start_time, #event_end_time' ).fadeOut( 300 );
			} else {
				$( '#event_start_time, #event_end_time' ).fadeIn( 300 );
			}
		} );


		// displays recurrence options based on type
		$( document ).on( 'change', '#event_recurrence', function () {
			var selected = $( this ).find( ':selected' ).val();

			if ( selected === 'once' ) {
				$( '#event_recurrence_types' ).hide();
				$( '#event_custom_occurrences' ).hide();
			} else if ( selected === 'custom' ) {
				$( '#event_recurrence_types' ).hide();
				$( '#event_custom_occurrences' ).show();
			} else {
				var repeat = parseInt( $( 'input[name="event_recurrence[repeat]"]' ).val() );

				$( '#event_custom_occurrences' ).hide();
				$( '#event_recurrence_types' ).show();

				if ( selected === 'daily' ) {
					$( '#event_recurrence_types div.monthly' ).hide();
					$( '#event_recurrence_types div.weekly' ).hide();

					$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.days : emPostArgs.day );
				}
				else if ( selected === 'weekly' ) {
					$( '#event_recurrence_types div.weekly' ).show();
					$( '#event_recurrence_types div.monthly' ).hide();

					$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.weeks : emPostArgs.week );
				} else if ( selected === 'monthly' ) {
					$( '#event_recurrence_types div.monthly' ).show();
					$( '#event_recurrence_types div.weekly' ).hide();

					$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.months : emPostArgs.month );
				} else {
					$( '#event_recurrence_types div.monthly' ).hide();
					$( '#event_recurrence_types div.weekly' ).hide();

					$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.years : emPostArgs.year );
				}
			}
		} );


		// prevents to put invalid numbers into repeat field
		$( document ).on( 'change', 'input[name="event_recurrence[repeat]"]', function () {
			var repeat = parseInt( $( this ).val() ),
					selected = $( '#event_recurrence' ).find( ':selected' ).val();

			if ( isNaN( repeat ) ) {
				$( 'input[name="event_recurrence[repeat]"]' ).val( 1 );
			} else {
				$( 'input[name="event_recurrence[repeat]"]' ).val( repeat > 0 ? repeat : 1 );
			}

			if ( selected === 'daily' ) {
				$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.days : emPostArgs.day );
			}
			else if ( selected === 'weekly' ) {
				$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.weeks : emPostArgs.week );
			} else if ( selected === 'monthly' ) {
				$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.months : emPostArgs.month );
			} else {
				$( '#event_recurrence_types span.occurrence' ).text( repeat > 1 ? emPostArgs.years : emPostArgs.year );
			}
		} );


		// adds new custom occurrence
		$( document ).on( 'click', '#add-custom-event', function () {
			// adds new custom occurrence
			$( '#event_custom_occurrences' ).append( cloneTemplate( $( '#event-custom-template' ) ) );

			var last = $( '#event_custom_occurrences' ).find( '.event-custom:last' );

			// displays just added custom occurrence
			last.fadeIn( 300 );

			// adds datepicker
			last.find( '.event_custom_date' ).datepicker( dateOptions );
			last.find( '.start .event_custom_date' ).datepicker( 'option', 'minDate', new Date( $( '#event_start_date' ).val() ) );
			last.find( '.end .event_custom_date' ).datepicker( 'option', 'minDate', new Date( $( '#event_end_date' ).val() ) );

			// adds timepicker
			last.find( '.event_custom_time' ).timepicker( timeOptions );

			return false;
		} );


		// deletes custom occurrence
		$( document ).on( 'click', '.delete-custom-event', function () {
			if ( confirm( emPostArgs.deleteCustomOccurrence ) ) {
				var occurrence = $( this ).closest( '.event-custom' );

				occurrence.fadeOut( 300, function () {
					occurrence.remove();
				} );
			}

			return false;
		} );


		// is it event with separate end date?
		$( document ).on( 'change', '.event_separate', function () {
			if ( $( this ).is( ':checked' ) ) {
				$( this ).parent().find( '.end' ).fadeIn( 300 );
			} else {
				$( this ).parent().find( '.end' ).fadeOut( 300 );
			}

			return false;
		} );


		// sets valid day for weekly recurrence if needed
		$( document ).on( 'change', '#event_recurrence_types .weekly input', function () {
			var checkedDays = [];

			$( '#event_recurrence_types .weekly input' ).each( function ( i, item ) {
				var item = $( item );

				if ( item.is( ':checked' ) ) {
					checkedDays[item.val()] = true;
				}
			} );

			if ( checkedDays.length === 0 ) {
				var start = $.trim( $( '#event_start_date' ).val() );

				if ( start === '' ) {
					startDay = 1;
				} else {
					date = new Date( start );
					startDay = ( ( startDay = date.getDay() ) == 0 ? 7 : startDay );
				}

				$( '#event_recurrence_weekday_' + startDay ).prop( 'checked', true );
			}

			return false;
		} );


		// clones custom occurrence template
		function cloneTemplate( element )
		{
			var html = element.html();

			html = html.replace( /___EVENT_CUSTOM_DATE___/g, 'event_recurrence[custom]' );
			html = html.replace( /___ID___/g, customEventsCount++ );

			return html;
		}


		// Event gallery file uploads
		var event_gallery_frame;
		var $event_gallery_ids = $( '#event_gallery' );
		var $event_images = $( '#event_gallery_container ul.event_images' );

		$( '.add_event_images' ).on( 'click', 'a', function ( event ) {
			var $el = $( this );
			var attachment_ids = $event_gallery_ids.val();

			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( event_gallery_frame ) {
				event_gallery_frame.open();
				return;
			}

			// Create the media frame.
			event_gallery_frame = wp.media.frames.event_gallery = wp.media( {
				// Set the title of the modal.
				title: $el.data( 'choose' ),
				button: {
					text: $el.data( 'update' ),
				},
				library: {
					type: 'image'
				},
				multiple: true
			} );

			event_gallery_frame.on( 'open', function () {
				var selection = event_gallery_frame.state().get( 'selection' );
				var attachment_ids = $event_gallery_ids.val().split( ',' );

				$.each( attachment_ids, function () {
					if ( $.isNumeric( this ) ) {
						attachment = wp.media.attachment( this );
						attachment.fetch();
						selection.add( attachment ? [attachment] : [] );
					}
				} );
			} );

			// When an image is selected, run a callback.
			event_gallery_frame.on( 'select', function () {

				var selection = event_gallery_frame.state().get( 'selection' );
				var attachment_ids = $event_gallery_ids.val().split( ',' );

				if ( selection )
				{
					selection.map( function ( attachment ) {

						// is image already in gallery?
						if ( $event_images.find( '.image[data-attachment_id="' + attachment.id + '"]' ).length )
						{
							return;
						}

						$event_gallery_ids.val()

						if ( attachment.id ) {

							attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

							attachment = attachment.toJSON();

							// is preview size available?
							if ( attachment.sizes && attachment.sizes['thumbnail'] ) {
								attachment.url = attachment.sizes['thumbnail'].url;
							}

							$event_images.append( '\
								<li class="image" data-attachment_id="' + attachment.id + '">\
									<div class="inner"><img src="' + attachment.url + '" /></div>\
									<div class="actions"><a href="#" class="delete dashicons dashicons-no" title="' + $el.data( 'delete' ) + '"></a></div>\
								</li>'
									);
						}
					} );
				}
				;

				$event_gallery_ids.val( attachment_ids );
			} );

			// Finally, open the modal.
			event_gallery_frame.open();
		} );

		// Image ordering
		$event_images.sortable( {
			items: 'li.image',
			cursor: 'move',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			forceHelperSize: false,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'event-gallery-sortable-placeholder',
			start: function ( event, ui ) {
				ui.item.css( 'border-color', '#f6f6f6' );
			},
			stop: function ( event, ui ) {
				ui.item.removeAttr( 'style' );
			},
			update: function ( event, ui ) {
				var attachment_ids = '';

				$( '#event_gallery_container ul li.image' ).each( function () {
					var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				} );

				$event_gallery_ids.val( attachment_ids );
			}
		} );

		// Remove images
		$( '#event_gallery_container' ).on( 'click', 'a.delete', function () {
			$( this ).closest( 'li.image' ).remove();

			var attachment_ids = '';

			$( '#event_gallery_container ul li.image' ).each( function () {
				var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
				attachment_ids = attachment_ids + attachment_id + ',';
			} );

			$event_gallery_ids.val( attachment_ids );

			return false;
		} );

	} );

} )( jQuery );