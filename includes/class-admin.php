<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Admin();

/**
 * Events_Maker_Admin Class.
 */
class Events_Maker_Admin {
	
	private $notices = array();

	public function __construct() {
		// set instance
		Events_Maker()->admin = $this;

		// actions
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts_styles' ) );
		add_action( 'admin_notices', array( &$this, 'pages_notice' ), 9 );
		add_action( 'deleted_post', array( &$this, 'deleted_post_action_page' ) );
		add_action( 'transition_post_status', array( &$this, 'transition_post_status_action_page' ), 10, 3 );
	}
	
	/**
	 * Add admin notices
	 */
	public function add_notice( $html = '', $status = 'error', $paragraph = false, $network = true ) {
		$this->notices[] = array(
			'html' 		=> $html,
			'status' 	=> $status,
			'paragraph' => $paragraph
		);

		add_action( 'admin_notices', array( &$this, 'display_notice') );

		if( $network )
			add_action( 'network_admin_notices', array( &$this, 'display_notice') );
	}

	/**
	 * Print admin notices
	 */
	public function display_notice() {
		foreach( $this->notices as $notice ) {
			echo '
			<div class="events-maker ' . $notice['status'] . '">
				' . ( $notice['paragraph'] ? '<p>' : '' ) . '
				' . $notice['html'] . '
				' . ( $notice['paragraph'] ? '</p>' : '' ) . '
			</div>';
		}
	}

	/**
	 * Pages admin notice.
	 */
	public function pages_notice() {
		if ( ! current_user_can( 'manage_options' ) )
			return false;

		// hide action pages notice
		if ( isset( $_GET['em_action'] ) && $_GET['em_action'] == 'decline_pages' ) {
			Events_Maker()->options['general']['pages_notice'] = false;
			update_option( 'events_maker_general', Events_Maker()->options['general'] );
		}

		// display action pages notice
		if ( Events_Maker()->options['general']['pages_notice'] ) {
			global $pagenow;

			// get current admin url
			$query_string = array();
			parse_str( $_SERVER['QUERY_STRING'], $query_string );
			$current_url = esc_url( add_query_arg( array_merge( (array) $query_string, array( 'em_action' => 'decline_pages' ) ), '', admin_url( trailingslashit( $pagenow ) ) ) );
			
			$this->add_notice( __( '<strong>Events Maker:</strong> One or more pages needs to be set up in order to make your events work properly.', 'events-maker' ) . ' <a href="' . esc_url( admin_url( 'edit.php?post_type=event&page=events-settings&tab=display' ) ) . '" class="button button-primary">' . esc_html__( 'Setup pages', 'events-maker' ) . '</a> <a href="' . esc_url( $current_url ) . '" class="button button-secondary">' . esc_html__( 'No, thank you', 'events-maker' ) . '</a>', 'error', true );
		}
	}

	/**
	 * Enqueue admin scripts and style.
	 */
	public function admin_scripts_styles( $pagenow ) {
		$screen = get_current_screen();

		wp_register_style(
			'events-maker-admin', EVENTS_MAKER_URL . '/css/admin.css'
		);

		wp_register_style(
			'events-maker-wplike', EVENTS_MAKER_URL . '/css/wp-like-ui-theme.css'
		);

		if ( $pagenow === 'edit-tags.php' && in_array( $screen->post_type, apply_filters( 'em_event_post_type', array( 'event' ) ) ) ) {
			// event location & organizer
			if ( ($screen->id === 'edit-event-organizer' && $screen->taxonomy === 'event-organizer') || ($screen->id === 'edit-event-location' && $screen->taxonomy === 'event-location') || ($screen->id === 'edit-event-category' && $screen->taxonomy === 'event-category') ) {
				$timezone = explode( '/', get_option( 'timezone_string' ) );

				if ( ! isset( $timezone[1] ) )
					$timezone[1] = 'United Kingdom, London';

				wp_enqueue_media();
				wp_enqueue_style( 'wp-color-picker' );

				wp_register_script(
					'events-maker-edit-tags', EVENTS_MAKER_URL . '/js/admin-tags.js', array( 'jquery', 'wp-color-picker', 'jquery-touch-punch' )
				);

				wp_enqueue_script( 'events-maker-edit-tags' );

				wp_register_script(
					'events-maker-google-maps', 'https://maps.googleapis.com/maps/api/js?sensor=false&language=' . substr( get_locale(), 0, 2 )
				);

				// on event locations only
				if ( $screen->id === 'edit-event-location' )
					wp_enqueue_script( 'events-maker-google-maps' );

				wp_localize_script(
					'events-maker-edit-tags', 'emArgs', array(
					'title'		 => __( 'Select image', 'events-maker' ),
					'button'	 => array( 'text' => __( 'Add image', 'events-maker' ) ),
					'frame'		 => 'select',
					'multiple'	 => false,
					'country'	 => $timezone[1]
					)
				);

				wp_enqueue_style( 'events-maker-admin' );
			}
		}
		// widgets
		elseif ( $pagenow === 'widgets.php' ) {
			wp_register_script(
				'events-maker-admin-widgets', EVENTS_MAKER_URL . '/js/admin-widgets.js', array( 'jquery' )
			);

			wp_enqueue_script( 'events-maker-admin-widgets' );
			wp_enqueue_style( 'events-maker-admin' );
		}
		// event options page
		elseif ( $pagenow === 'event_page_events-settings' ) {
			wp_register_script(
				'events-maker-admin-settings', EVENTS_MAKER_URL . '/js/admin-settings.js', array( 'jquery' )
			);

			wp_enqueue_script( 'events-maker-admin-settings' );

			wp_localize_script(
				'events-maker-admin-settings', 'emArgs', array(
				'resetToDefaults' => __( 'Are you sure you want to reset these settings to defaults?', 'events-maker' )
				)
			);

			wp_enqueue_style( 'events-maker-admin' );
		}
		// list of events
		elseif ( $pagenow === 'edit.php' && in_array( $screen->post_type, apply_filters( 'em_event_post_type', array( 'event' ) ) ) ) {
			global $wp_locale;

			wp_register_script(
				'events-maker-admin-edit', EVENTS_MAKER_URL . '/js/admin-edit.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' )
			);

			wp_enqueue_script( 'events-maker-admin-edit' );

			wp_localize_script(
				'events-maker-admin-edit', 'emEditArgs', array(
				'firstWeekDay'		 => Events_Maker()->options['general']['first_weekday'],
				'monthNames'		 => array_values( $wp_locale->month ),
				'monthNamesShort'	 => array_values( $wp_locale->month_abbrev ),
				'dayNames'			 => array_values( $wp_locale->weekday ),
				'dayNamesShort'		 => array_values( $wp_locale->weekday_abbrev ),
				'dayNamesMin'		 => array_values( $wp_locale->weekday_initial ),
				'isRTL'				 => $wp_locale->is_rtl(),
				'nonce'				 => wp_create_nonce( 'events-maker-feature-event' )
				)
			);

			wp_enqueue_style( 'events-maker-admin' );
			wp_enqueue_style( 'events-maker-wplike' );
		}
		// update
		elseif ( $pagenow === 'event_page_events-maker-update' )
			wp_enqueue_style( 'events-maker-admin' );
	}

	/**
	 * Get plugin action page id.
	 */
	public function get_action_page_id( $action_pages = array() ) {
		$ids = array();

		if ( empty( $action_pages ) )
			$pages = Events_Maker()->options['general']['pages'];
		else
			$pages = $action_pages;

		if ( ! empty( $pages ) ) {
			if ( is_array( $pages ) ) {
				foreach ( $pages as $key => $action ) {
					$ids[$key] = (int) $action['id'];

					// wpml and polylang compatibility
					if ( function_exists( 'icl_object_id' ) )
						$ids[$key] = (int) icl_object_id( (int) $action['id'], 'page', true );
				}
			} elseif ( is_string( $pages ) ) {
				$ids = isset( Events_Maker()->options['general']['pages'][$pages]['id'] ) ? (int) Events_Maker()->options['general']['pages'][$pages]['id'] : (int) Events_Maker()->defaults['general']['pages'][$pages]['id'];

				// wpml and polylang compatibility
				if ( function_exists( 'icl_object_id' ) )
					$ids = (int) icl_object_id( $ids, 'page', true );
			}
		}
		
		return $ids;
	}

	/**
	 * Check whether all action pages are set, valid and unique.
	 */
	public function is_action_page_set( $pages = array() ) {
		// gets action pages ids
		$pages_ids = $this->get_action_page_id( $pages );

		if ( count( array_keys( $pages_ids, 0, true ) ) === 0 )
			return true;
		else
			return false;
	}

	/**
	 * Check pages on delete or trash.
	 */
	public function deleted_post_action_page( $post_id ) {
		if ( get_post_type( $post_id ) === 'page' && in_array( $post_id, ( $ids = $this->get_action_page_id() ), true ) ) {
			foreach ( array_keys( $ids, $post_id, true ) as $page ) {
				Events_Maker()->options['general']['pages'][$page]['id'] = 0;
			}

			Events_Maker()->options['general']['pages_notice'] = ! $this->is_action_page_set( Events_Maker()->options['general']['pages'] );

			update_option( 'events_maker_general', Events_Maker()->options['general'] );
		}
	}

	/**
	 * Check pages on post status change.
	 */
	public function transition_post_status_action_page( $new_status, $old_status, $post ) {
		if ( $post->post_type === 'page' && $old_status === 'publish' && $new_status !== 'publish' && in_array( (int) $post->ID, ( $ids = $this->get_action_page_id() ), true ) ) {
			foreach ( array_keys( $ids, (int) $post->ID, true ) as $page ) {
				Events_Maker()->options['general']['pages'][$page]['id'] = 0;
			}

			Events_Maker()->options['general']['pages_notice'] = ! $this->is_action_page_set( Events_Maker()->options['general']['pages'] );

			update_option( 'events_maker_general', Events_Maker()->options['general'] );
		}
	}

}
