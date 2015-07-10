<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Shortcodes();

/**
 * Events_Maker_Shortcodes Class.
 */
class Events_Maker_Shortcodes {

	public function __construct() {
		// set instance
		Events_Maker()->shortcodes = $this;

		// actions
		add_action( 'init', array( &$this, 'register_shortcodes' ) );

		// filters
		add_filter( 'the_content', array( &$this, 'extend_the_content' ) );
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes() {
		add_shortcode( 'em-events', array( &$this, 'events_shortcode' ) );
		add_shortcode( 'em-full-calendar', array( &$this, 'calendar_shortcode' ) );
		add_shortcode( 'em-google-map', array( &$this, 'google_map_shortcode' ) );
		add_shortcode( 'em-locations-list', array( &$this, 'event_locations_shortcode' ) );
		add_shortcode( 'em-organizers-list', array( &$this, 'event_organizers_shortcode' ) );
	}

	/**
	 * Display pages content.
	 */
	public function extend_the_content( $content ) {
		$action_pages = Events_Maker()->admin->get_action_page_id();

		if ( is_page() ) {
			$page_id = get_the_ID();

			if ( in_array( $page_id, array_values( $action_pages ) ) ) {
				$key = array_search( $page_id, $action_pages );

				if ( ! empty( $key ) ) {
					$shortcode = '';

					switch ( $key ) {
						case 'calendar':
							$shortcode = '[em-full-calendar]';
							break;

						case 'past_events':
							$shortcode = '[em-events show_past_events="1" order="DESC" start_before="NOW"]';
							break;

						case 'locations':
							$shortcode = '[em-locations-list]';
							break;

						case 'organizers':
							$shortcode = '[em-organizers-list]';
							break;
					}

					if ( ! empty( $shortcode ) ) {
						if ( isset( Events_Maker()->options['general']['pages'][$key]['id'] ) ) {
							$position = Events_Maker()->options['general']['pages'][$key]['position'];
						// backward compatibility
						} elseif ( $key === 'calendar' && isset( Events_Maker()->options['general']['full_calendar_display'] ) ) {
							if ( Events_Maker()->options['general']['full_calendar_display']['type'] === 'page' ) {
								$position = Events_Maker()->options['general']['full_calendar_display']['content'];
							}
						} else {
							$position = Events_Maker()->defaults['general']['pages'][$key]['position'];
						}

						switch ( $position ) {
							case 'before':
								$content = $shortcode . $content;
								break;

							case 'after':
								$content = $content . $shortcode;
								break;
						}
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Events list shortcode.
	 */
	public function events_shortcode( $args ) {
		$defaults = array(
			'start_after'		 => '',
			'start_before'		 => '',
			'end_after'			 => '',
			'end_before'		 => '',
			'ondate'			 => '',
			'date_range'		 => 'between',
			'date_type'			 => 'all',
			'ticket_type'		 => 'all',
			'show_past_events'	 => Events_Maker()->options['general']['show_past_events'],
			'show_occurrences'	 => Events_Maker()->options['general']['show_occurrences'],
			'number_of_events'	 => get_option( 'posts_per_page' ),
			'featured_only'		 => false,
			'disable_pagination' => false,
			'offset'			 => '',
			'categories'		 => '',
			'locations'			 => '',
			'organizers'		 => '',
			'orderby'			 => 'start',
			'order'				 => 'asc',
			'author'			 => '',
			'style'				 => 'loop'
		);

		// parse arguments
		$args = shortcode_atts( $defaults, $args );

		// makes strings
		$args['start_after'] = (string) $args['start_after'];
		$args['start_before'] = (string) $args['start_before'];
		$args['end_after'] = (string) $args['end_after'];
		$args['end_before'] = (string) $args['end_before'];
		$args['ondate'] = (string) $args['ondate'];
		$args['orderby'] = (string) $args['orderby'];
		$args['order'] = (string) $args['order'];
		$args['categories'] = (string) $args['categories'];
		$args['locations'] = (string) $args['locations'];
		$args['organizers'] = (string) $args['organizers'];

		// valid date range?
		if ( ! in_array( $args['date_range'], array( 'between', 'outside' ), true ) )
			$args['date_range'] = $defaults['date_range'];

		// valid date type?
		if ( ! in_array( $args['date_type'], array( 'all', 'all_day', 'not_all_day' ), true ) )
			$args['date_type'] = $defaults['date_type'];

		// valid ticket type?
		if ( ! in_array( $args['ticket_type'], array( 'all', 'free', 'paid' ), true ) )
			$args['ticket_type'] = $defaults['ticket_type'];

		$authors = $users = array();

		if ( trim( $args['author'] ) !== '' )
			$users = explode( ',', $args['author'] );

		if ( ! empty( $users ) ) {
			foreach ( $users as $author ) {
				$authors[] = (int) $author;
			}

			// remove possible duplicates
			$events_args['author__in'] = array_unique( $authors );
		}

		// valid style?
		if ( ! in_array( $args['style'], array( 'loop', 'widget' ), true ) )
			$args['style'] = $defaults['style'];

		if ( $args['style'] == 'widget' )
			$template = 'content-widget-event.php';
		else
			$template = 'content-event.php';

		// set new arguments
		$events_args['event_start_after'] = $args['start_after'];
		$events_args['event_start_before'] = $args['start_before'];
		$events_args['event_end_after'] = $args['end_after'];
		$events_args['event_end_before'] = $args['end_before'];
		$events_args['event_ondate'] = $args['ondate'];
		$events_args['event_date_range'] = $args['date_range'];
		$events_args['event_date_type'] = $args['date_type'];
		$events_args['event_ticket_type'] = $args['ticket_type'];
		$events_args['event_show_past_events'] = (bool) (int) $args['show_past_events'];
		$events_args['event_show_occurrences'] = (bool) (int) $args['show_occurrences'];
		$events_args['event_show_featured'] = (bool) (int) $args['featured_only'];
		$events_args['orderby'] = (string) $args['orderby'];
		$events_args['order'] = (string) $args['order'];
		$events_args['post_type'] = 'event';
		$events_args['suppress_filters'] = false;
		$events_args['posts_per_page'] = (int) $args['number_of_events'];
		$events_args['offset'] = (int) $args['offset'];
		$events_args['paged'] = (get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1);

		if ( ! empty( $args['categories'] ) ) {
			$events_args['tax_query'][] = array(
				'taxonomy'			 => 'event-category',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['categories'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		if ( ! empty( $args['locations'] ) ) {
			$events_args['tax_query'][] = array(
				'taxonomy'			 => 'event-location',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['locations'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		if ( ! empty( $args['organizers'] ) ) {
			$events_args['tax_query'][] = array(
				'taxonomy'			 => 'event-organizer',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['organizers'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		global $wp_query;

		// replace global wp_query with events query
		$temp_query = $wp_query;
		$wp_query = new WP_Query( $events_args );

		if ( $wp_query->have_posts() ) {
			ob_start();

			foreach ( $wp_query->posts as $post ) {
				setup_postdata( $post );

				em_get_template( $template, array( $post, $args ) );
			}

			wp_reset_postdata();
		} else {
			ob_start();
			?>
			<article id="post-0" class="post no-results not-found">

				<div class="entry-content">

					<p><?php _e( 'Apologies, but no events were found.', 'events-maker' ); ?></p>

				</div>

			</article>
			<?php
		}

		// display pagination
		if ( (bool) (int) $args['disable_pagination'] != true )
			em_paginate_links();

		$html = ob_get_contents();
		ob_end_clean();

		// restore original query
		$wp_query = $temp_query;

		return apply_filters( 'em_shortcode_events', $html );
	}

	/**
	 * Events full calendar shortcode.
	 */
	public function calendar_shortcode( $args ) {
		$defaults = array(
			'start_after'		 => '',
			'start_before'		 => '',
			'end_after'			 => '',
			'end_before'		 => '',
			'ondate'			 => '',
			'date_range'		 => 'between',
			'date_type'			 => 'all',
			'ticket_type'		 => 'all',
			'show_past_events'	 => Events_Maker()->options['general']['show_past_events'],
			'show_occurrences'	 => Events_Maker()->options['general']['show_occurrences'],
			'categories'		 => '',
			'locations'			 => '',
			'organizers'		 => '',
			'post_type'			 => 'event',
			'author'			 => ''
		);

		// parse arguments
		$args = shortcode_atts( $defaults, $args );

		// make strings
		$args['start_after'] = (string) $args['start_after'];
		$args['start_before'] = (string) $args['start_before'];
		$args['end_after'] = (string) $args['end_after'];
		$args['end_before'] = (string) $args['end_before'];
		$args['ondate'] = (string) $args['ondate'];

		// valid date range?
		if ( ! in_array( $args['date_range'], array( 'between', 'outside' ), true ) )
			$args['date_range'] = $defaults['date_range'];

		// valid date type?
		if ( ! in_array( $args['date_type'], array( 'all', 'all_day', 'not_all_day' ), true ) )
			$args['date_type'] = $defaults['date_type'];

		// valid ticket type?
		if ( ! in_array( $args['ticket_type'], array( 'all', 'free', 'paid' ), true ) )
			$args['ticket_type'] = $defaults['ticket_type'];

		// make bitwise integers
		$args['show_past_events'] = (bool) (int) $args['show_past_events'];
		$args['show_occurrences'] = (bool) (int) $args['show_occurrences'];

		$authors = $users = array();

		if ( trim( $args['author'] ) !== '' )
			$users = explode( ',', $args['author'] );

		if ( ! empty( $users ) ) {
			foreach ( $users as $author ) {
				$authors[] = (int) $author;
			}

			// remove possible duplicates
			$args['author__in'] = array_unique( $authors );
		}

		// unset author argument
		unset( $args['author'] );

		// set new arguments
		$args['event_start_after'] = $args['start_after'];
		$args['event_start_before'] = $args['start_before'];
		$args['event_end_after'] = $args['end_after'];
		$args['event_end_before'] = $args['end_before'];
		$args['event_ondate'] = $args['ondate'];
		$args['event_date_range'] = $args['date_range'];
		$args['event_date_type'] = $args['date_type'];
		$args['event_ticket_type'] = $args['ticket_type'];
		$args['event_show_past_events'] = $args['show_past_events'];
		$args['event_show_occurrences'] = $args['show_occurrences'];

		// unset old arguments
		unset( $args['start_after'] );
		unset( $args['start_before'] );
		unset( $args['end_after'] );
		unset( $args['end_before'] );
		unset( $args['ondate'] );
		unset( $args['date_range'] );
		unset( $args['date_type'] );
		unset( $args['ticket_type'] );
		unset( $args['show_past_events'] );
		unset( $args['show_occurrences'] );

		if ( ! empty( $args['categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy'			 => 'event-category',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['categories'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		if ( ! empty( $args['locations'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy'			 => 'event-location',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['locations'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		if ( ! empty( $args['organizers'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy'			 => 'event-organizer',
				'field'				 => 'id',
				'terms'				 => explode( ',', $args['organizers'] ),
				'include_children'	 => false,
				'operator'			 => 'IN'
			);
		}

		unset( $args['categories'] );
		unset( $args['locations'] );
		unset( $args['organizers'] );

		wp_register_script(
			'events-maker-moment', EVENTS_MAKER_URL . '/assets/fullcalendar/moment.min.js', array( 'jquery' )
		);

		wp_register_script(
			'events-maker-fullcalendar', EVENTS_MAKER_URL . '/assets/fullcalendar/fullcalendar.min.js', array( 'jquery', 'events-maker-moment' )
		);

		wp_register_script(
			'events-maker-front-calendar', EVENTS_MAKER_URL . '/js/front-calendar.js', array( 'jquery', 'jquery-ui-core', 'events-maker-fullcalendar' )
		);

		wp_enqueue_script( 'events-maker-front-calendar' );

		$locale = str_replace( '_', '-', strtolower( get_locale() ) );
		$locale_code = explode( '-', $locale );

		if ( file_exists( EVENTS_MAKER_PATH . 'assets/fullcalendar/lang/' . $locale . '.js' ) )
			$lang_path = EVENTS_MAKER_URL . '/assets/fullcalendar/lang/' . $locale . '.js';
		elseif ( file_exists( EVENTS_MAKER_PATH . 'assets/fullcalendar/lang/' . $locale_code[0] . '.js' ) )
			$lang_path = EVENTS_MAKER_URL . '/assets/fullcalendar/lang/' . $locale_code[0] . '.js';

		if ( isset( $lang_path ) ) {
			wp_register_script(
				'events-maker-front-calendar-lang', $lang_path, array( 'jquery', 'jquery-ui-core', 'events-maker-front-calendar' )
			);

			wp_enqueue_script( 'events-maker-front-calendar-lang' );
		}

		// calendar events query
		$args = apply_filters( 'em_get_full_calendar_events_args', $args );
		
		// script args
		$script_args = apply_filters( 'em_get_full_calendar_script_args', array(
			'firstWeekDay'	 	=> ( Events_Maker()->options['general']['first_weekday'] === 7 ? 0 : 1 ),
			'timeFormat'	 	=> str_replace( array( 's', 'i', 'H', 'h', 'G', 'g' ), array( 'ss', 'mm', 'HH', 'hh', 'H', 'h' ), Events_Maker()->options['general']['datetime_format']['time'] ),
			'columnFormat'		=> array(
				'month' 			=> 'ddd',
				'week' 				=> 'ddd, DD/MM',
				'day' 				=> 'dddd'
			),
			'header'			=> array(
				'left'				=> 'prev,next today',
				'center'			=> 'title',
				'right'				=> 'month,agendaWeek,agendaDay'
			),
			'editable'			=> false,
			'fixedWeekCount'	=> false,
			'events'		 	=> $this->get_full_calendar_events( $args )
		) );

		wp_localize_script(
			'events-maker-front-calendar', 'emCalendarArgs', json_encode( $script_args )
		);

		wp_register_style(
			'events-maker-front-calendar', EVENTS_MAKER_URL . '/assets/fullcalendar/fullcalendar.min.css'
		);

		wp_register_style(
			'events-maker-front-calendar-print', EVENTS_MAKER_URL . '/assets/fullcalendar/fullcalendar.print.css', array(), false, 'print'
		);

		wp_enqueue_style( 'events-maker-front-calendar' );
		wp_enqueue_style( 'events-maker-front-calendar-print' );

		$html = '<div id="events-full-calendar"></div>';

		return apply_filters( 'em_shortcode_full_calendar', $html );
	}

	/**
	 * Get events for calendar display.
	 */
	private function get_full_calendar_events( $args ) {
		
		// get calendar query transient / cache
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			// WPML & Polylang support
			$calendar = get_transient( 'em_calendar_query' . '-' . ICL_LANGUAGE_CODE );
		} else {
			$calendar = get_transient( 'em_calendar_query' );
		}

		if ( $calendar === false ) {
			$events = em_get_events( $args );
			$calendar = array();

			if ( empty( $events ) )
				return $calendar;

			foreach ( $events as $event ) {
				$classes = array();
				$event_categories = wp_get_post_terms( $event->ID, 'event-category' );
				$event_tags = wp_get_post_terms( $event->ID, 'event-tag' );

				if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) {
					$term_meta = get_option( 'event_category_' . $event_categories[0]->term_taxonomy_id );

					foreach ( $event_categories as $category ) {
						$classes[] = "fc-event-cat-" . $category->slug;
						$classes[] = "fc-event-cat-" . $category->term_id;
					}
				}

				if ( ! empty( $event_tags ) && ! is_wp_error( $event_tags ) ) {
					foreach ( $event_tags as $tag ) {
						$classes[] = "fc-event-tag-" . $tag->slug;
						$classes[] = "fc-event-tag-" . $tag->term_id;
					}
				}

				if ( em_is_recurring( $event->ID ) && Events_Maker()->options['general']['show_occurrences'] ) {
					$start = $event->event_occurrence_start_date;
					$end = $event->event_occurrence_end_date;
				} else {
					$start = $event->_event_start_date;
					$end = $event->_event_end_date;
				}

				$all_day_event = em_is_all_day( $event->ID );

				$calendar[] = apply_filters( 'em_calendar_event_data', 
					array(
						'title'				 => $event->post_title,
						'start'				 => $start,
						'end'				 => ($all_day_event ? date( 'Y-m-d H:i:s', strtotime( $end . '+1 day' ) ) : $end),
						'className'			 => implode( ' ', $classes ),
						'allDay'			 => $all_day_event,
						'url'				 => get_permalink( $event->ID ),
						'backgroundColor'	 => (isset( $term_meta['color'] ) ? $term_meta['color'] : '')
					), $event
				);
			}

			$expiration = absint( apply_filters( 'em_calendar_query_expiration', 24 * 60 * 60 ) );

			// set calendar query transient / cache
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				// WPML & Polylang support
				set_transient( 'em_calendar_query' . '-' . ICL_LANGUAGE_CODE, $calendar, $expiration );
			} else {
				set_transient( 'em_calendar_query', $calendar, $expiration );
			}
		}

		return apply_filters( 'em_shortcode_full_calendar_events', $calendar );
	}

	/**
	 * Google map shortcode.
	 */
	public function google_map_shortcode( $args ) {
		$markers = array();
		$map_types = array( 'hybrid', 'roadmap', 'satellite', 'terrain' );
		$booleans = array( 'on', 'off' );
		$defaults = array(
			'width'				 => '100%',
			'height'			 => '300px',
			'zoom'				 => 15,
			'maptype'			 => 'roadmap',
			'locations'			 => '',
			'maptypecontrol'	 => 'on',
			'zoomcontrol'		 => 'on',
			'streetviewcontrol'	 => 'on',
			'overviewmapcontrol' => 'off',
			'pancontrol'		 => 'off',
			'rotatecontrol'		 => 'off',
			'scalecontrol'		 => 'off',
			'draggable'			 => 'on',
			'keyboardshortcuts'	 => 'on',
			'scrollzoom'		 => 'on'
		);

		$args = shortcode_atts( $defaults, $args );
		$args['zoom'] = (int) $args['zoom'];

		if ( ! in_array( strtolower( $args['maptype'] ), $map_types, TRUE ) )
			$args['maptype'] = $defaults['maptype'];

		$args['maptype'] = strtoupper( $args['maptype'] );
		$args['maptypecontrol'] = $this->get_proper_arg( $args['maptypecontrol'], $defaults['maptypecontrol'], $booleans );
		$args['zoomcontrol'] = $this->get_proper_arg( $args['zoomcontrol'], $defaults['zoomcontrol'], $booleans );
		$args['streetviewcontrol'] = $this->get_proper_arg( $args['streetviewcontrol'], $defaults['streetviewcontrol'], $booleans );
		$args['overviewmapcontrol'] = $this->get_proper_arg( $args['overviewmapcontrol'], $defaults['overviewmapcontrol'], $booleans );
		$args['pancontrol'] = $this->get_proper_arg( $args['pancontrol'], $defaults['pancontrol'], $booleans );
		$args['rotatecontrol'] = $this->get_proper_arg( $args['rotatecontrol'], $defaults['rotatecontrol'], $booleans );
		$args['scalecontrol'] = $this->get_proper_arg( $args['scalecontrol'], $defaults['scalecontrol'], $booleans );
		$args['draggable'] = $this->get_proper_arg( $args['draggable'], $defaults['draggable'], $booleans );
		$args['keyboardshortcuts'] = $this->get_proper_arg( $args['keyboardshortcuts'], $defaults['keyboardshortcuts'], $booleans );
		$args['scrollzoom'] = $this->get_proper_arg( $args['scrollzoom'], $defaults['scrollzoom'], $booleans );

		// location ids
		$locations = ($args['locations'] !== '' ? explode( ',', $args['locations'] ) : '');

		if ( is_array( $locations ) && ! empty( $locations ) ) {
			$locations_tmp = array();

			foreach ( $locations as $location ) {
				$locations_tmp[] = (int) $location;
			}

			foreach ( array_unique( $locations_tmp ) as $location_id ) {
				$location = em_get_location( $location_id );

				if ( ! empty( $location->location_meta['google_map'] ) && is_array( $location->location_meta['google_map'] ) ) {
					$location->location_meta['name'] = $location->name;
					$location->location_meta['latitude'] = $location->location_meta['google_map']['latitude'];
					$location->location_meta['longitude'] = $location->location_meta['google_map']['longitude'];
					$markers[] = $location->location_meta;
				}
				// backward compatibility
				elseif ( ! empty( $location->location_meta['latitude'] ) && ! empty( $location->location_meta['longitude'] ) ) {
					$location->location_meta['name'] = $location->name;
					$markers[] = $location->location_meta;
				}
			}
		} elseif ( is_tax( 'event-location' ) || (in_array( get_post_type(), apply_filters( 'em_event_post_type', array( 'event' ) ) ) && is_single()) ) {
			$object = get_queried_object();

			// taxonomy page
			if ( isset( $object->term_id ) ) {
				$location = em_get_location( $object->term_id );

				if ( ! empty( $location->location_meta['google_map'] ) && is_array( $location->location_meta['google_map'] ) ) {
					$location->location_meta['name'] = $location->name;
					$location->location_meta['latitude'] = $location->location_meta['google_map']['latitude'];
					$location->location_meta['longitude'] = $location->location_meta['google_map']['longitude'];
					$markers[] = $location->location_meta;
				}
				// backward compatibility
				elseif ( ! empty( $location->location_meta['latitude'] ) && ! empty( $location->location_meta['longitude'] ) ) {
					$location->location_meta['name'] = $location->name;
					$markers[] = $location->location_meta;
				}
			}
			// single post page
			elseif ( isset( $object->ID ) ) {
				$locations = em_get_locations_for( $object->ID );

				if ( is_array( $locations ) && ! empty( $locations ) ) {
					foreach ( $locations as $location ) {
						if ( ! empty( $location->location_meta['google_map'] ) && is_array( $location->location_meta['google_map'] ) ) {
							$location->location_meta['name'] = $location->name;
							$location->location_meta['latitude'] = $location->location_meta['google_map']['latitude'];
							$location->location_meta['longitude'] = $location->location_meta['google_map']['longitude'];
							$markers[] = $location->location_meta;
						}
						// backward compatibility
						elseif ( ! empty( $location->location_meta['latitude'] ) && ! empty( $location->location_meta['longitude'] ) ) {
							$location->location_meta['name'] = $location->name;
							$markers[] = $location->location_meta;
						}
					}
				}
			}
		}

		wp_register_script(
			'events-maker-google-maps', 'https://maps.googleapis.com/maps/api/js?sensor=false&language=' . substr( get_locale(), 0, 2 )
		);

		wp_register_script(
			'events-maker-front-locations', EVENTS_MAKER_URL . '/js/front-locations.js', array( 'jquery', 'events-maker-google-maps' )
		);

		wp_enqueue_script( 'events-maker-front-locations' );

		wp_localize_script(
			'events-maker-front-locations', 'emMapArgs', array(
			'markers'			 => $markers,
			'zoom'				 => $args['zoom'],
			'mapTypeId'			 => $args['maptype'],
			'mapTypeControl'	 => $args['maptypecontrol'],
			'zoomControl'		 => $args['zoomcontrol'],
			'streetViewControl'	 => $args['streetviewcontrol'],
			'overviewMapControl' => $args['overviewmapcontrol'],
			'panControl'		 => $args['pancontrol'],
			'rotateControl'		 => $args['rotatecontrol'],
			'scaleControl'		 => $args['scalecontrol'],
			'draggable'			 => $args['draggable'],
			'keyboardShortcuts'	 => $args['keyboardshortcuts'],
			'scrollwheel'		 => $args['scrollzoom']
			)
		);

		$html = '<div id="event-google-map" style="width: ' . $args['width'] . '; height: ' . $args['height'] . ';"></div>';

		return apply_filters( 'em_shortcode_google_map', $html );
	}

	/**
	 * Event locations list shortcode.
	 */
	public function event_locations_shortcode( $args ) {
		ob_start();

		em_get_template_part( 'archive-taxonomy', 'event-location' );

		$html = ob_get_contents();
		ob_end_clean();

		return apply_filters( 'em_shortcode_event_locations', $html );
	}

	/**
	 * Event organizers list shortcode.
	 */
	public function event_organizers_shortcode( $args ) {
		ob_start();

		em_get_template_part( 'archive-taxonomy', 'event-organizer' );

		$html = ob_get_contents();
		ob_end_clean();

		return apply_filters( 'em_shortcode_event_organizers', $html );
	}

	/**
	 * Helper: convert string to boolean.
	 */
	private function get_proper_arg( $arg, $default, $array ) {
		$arg = strtolower( $arg );

		if ( ! in_array( $arg, $array, TRUE ) )
			$arg = $default;

		if ( $arg === 'on' )
			return 1;
		else
			return 0;
	}

}
