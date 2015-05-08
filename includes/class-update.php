<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Update();

/**
 * Events_Maker_Update Class.
 */
class Events_Maker_Update {

	public function __construct() {
		// set instance
		Events_Maker()->update = $this;

		// actions
		add_action( 'init', array( &$this, 'check_update' ) );
	}

	/**
	 *
	 */
	public function check_update() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// updating?
		if ( isset( $_POST['events_maker_update'], $_POST['events_maker_number'] ) ) {
			if ( $_POST['events_maker_number'] === 'update_1' ) {
				if ( is_multisite() && is_network_admin() ) {
					global $wpdb;

					$current_blog_id = $wpdb->blogid;
					$blogs_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT blog_id FROM ' . $wpdb->blogs, '' ) );

					foreach ( $blogs_ids as $blog_id ) {
						switch_to_blog( $blog_id );

						$current_db_version = get_option( 'events_maker_version', '1.0.0' );

						// update only not updated sites
						if ( version_compare( $current_db_version, EVENTS_MAKER_UPDATE_VERSION_1, '<=' ) ) {
							// run update
							$this->update_1();

							// update plugin version
							update_option( 'events_maker_version', Events_Maker()->defaults['version'] );
						}
					}

					switch_to_blog( $current_blog_id );
				} else {
					$this->update_1();

					// update plugin version
					update_option( 'events_maker_version', Events_Maker()->defaults['version'] );
				}

				Events_Maker()->admin->add_notice( __( 'Datebase was succesfully updated. Enjoy new features!', 'events-maker' ), 'updated', true );
			}
		}

		$update_1_html = '
		<form action="" method="post">
			<input type="hidden" name="events_maker_number" value="update_1"/>
			<p>' . __( '<strong>Events Maker:</strong> New features require a database update. Make sure you backup your database and then click.', 'events-maker' ) . ' <input type="submit" class="button button-primary" name="events_maker_update" value="' . __( 'Update', 'events-maker' ) . '"/></p>
		</form>';

		// is it multisite network page?
		if ( is_multisite() && is_network_admin() ) {
			global $wpdb;

			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT blog_id FROM ' . $wpdb->blogs, '' ) );
			$update_required = false;

			foreach ( $blogs_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				// get current database version
				$current_db_version = get_option( 'events_maker_version', '1.0.0' );

				// new version?
				if ( version_compare( $current_db_version, Events_Maker()->defaults['version'], '<' ) ) {
					// is update 1 required?
					if ( version_compare( $current_db_version, EVENTS_MAKER_UPDATE_VERSION_1, '<=' ) )
						$update_required = true;
					else
					// update plugin version
						update_option( 'events_maker_version', Events_Maker()->defaults['version'] );
				}
			}

			if ( $update_required )
				Events_Maker()->admin->add_notice( $update_1_html );

			switch_to_blog( $current_blog_id );
		}
		else {
			// get current database version
			$current_db_version = get_option( 'events_maker_version', '1.0.0' );

			// new version?
			if ( version_compare( $current_db_version, Events_Maker()->defaults['version'], '<' ) ) {
				// is update 1 required?
				if ( version_compare( $current_db_version, EVENTS_MAKER_UPDATE_VERSION_1, '<=' ) )
					Events_Maker()->admin->add_notice( $update_1_html );
				else
				// update plugin version
					update_option( 'events_maker_version', Events_Maker()->defaults['version'] );
			}
		}
	}

	/**
	 * 
	 */
	public function update_1() {
		$events = em_get_events();

		if ( ! empty( $events ) ) {
			$now = current_time( 'timestamp' );
			$recurrence = array(
				'type'				 => 'once',
				'repeat'			 => 1,
				'until'				 => date( 'Y-m-d', $now ),
				'weekly_days'		 => array( (int) date( 'N', $now ) ),
				'monthly_day_type'	 => 1,
				'separate_end_date'	 => array()
			);

			foreach ( $events as $event ) {
				// for faster usage
				$id = $event->ID;

				// get event dates
				$start = get_post_meta( $id, '_event_start_date', true );
				$end = get_post_meta( $id, '_event_end_date', true );

				// is it event from events maker?
				if ( ! empty( $start ) && ! empty( $end ) ) {
					// full mysql timestamp format for start and end dates
					$start = date( 'Y-m-d H:i:s', strtotime( $start ) );
					$end = date( 'Y-m-d H:i:s', strtotime( $end ) );

					// update start and end dates with new format
					update_post_meta( $id, '_event_start_date', $start );
					update_post_meta( $id, '_event_end_date', $end );

					// new format of date in postmeta
					$date = $start . '|' . $end;

					// add first occurrence (same as start and end dates)
					add_post_meta( $id, '_event_occurrence_date', $date );

					// add last occurrence (same as first)
					add_post_meta( $id, '_event_occurrence_last_date', $date );

					// add recurrence options
					add_post_meta( $id, '_event_recurrence', $recurrence );
				}
			}
		}
	}

}

?>