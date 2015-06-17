<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Query();

/**
 * Events_Maker_Query Class.
 */
class Events_Maker_Query {

	public function __construct() {
		// set instance
		Events_Maker()->query = $this;

		// actions
		add_action( 'init', array( &$this, 'register_rewrite' ) );
		add_action( 'pre_get_posts', array( &$this, 'pre_get_posts' ) );
		add_action( 'pre_get_posts', array( &$this, 'alter_orderby_query' ), 11 );

		// filters
		add_filter( 'query_vars', array( &$this, 'register_query_vars' ) );
		add_filter( 'parse_query', array( &$this, 'filter_events' ) );
		add_filter( 'posts_fields', array( &$this, 'posts_fields' ), 10, 2 );
		add_filter( 'posts_groupby', array( &$this, 'posts_groupby' ), 10, 2 );
		add_filter( 'posts_join', array( &$this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_where', array( &$this, 'posts_where' ), 10, 2 );
		add_filter( 'posts_orderby', array( &$this, 'posts_orderby' ), 10, 2 );
		add_filter( 'request', array( &$this, 'feed_request' ) );
		add_filter( 'request', array( &$this, 'alter_event_page_request' ) );
		// add_filter('the_posts', array(&$this, 'sticky_featured'));
	}

	/**
	 * New rewrite rules.
	 */
	public function register_rewrite() {
		global $wp_rewrite;

		$wp_rewrite->add_rewrite_tag(
			'%event_ondate%', '([0-9]{4}(?:/[0-9]{2}(?:/[0-9]{2})?)?)', 'post_type=event&event_ondate='
		);

		$wp_rewrite->add_permastruct(
			'event_ondate', Events_Maker()->options['permalinks']['event_rewrite_base'] . '/%event_ondate%', array(
			'with_front' => false
			)
		);

		// flush rewrite rules if needed
		if ( Events_Maker()->options['general']['rewrite_rules'] ) {
			// set to false
			Events_Maker()->options['general']['rewrite_rules'] = false;
			update_option( 'events_maker_general', Events_Maker()->options['general'] );

			// flush rules
			flush_rewrite_rules();
		}
	}

	/**
	 * Filter events.
	 */
	public function filter_events( $query ) {
		if ( is_admin() ) {
			global $pagenow;

			$post_types = apply_filters( 'em_event_post_type', array( 'event' ) );

			foreach ( $post_types as $post_type ) {
				if ( $pagenow === 'edit.php' && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === $post_type ) {
					$em_helper = new Events_Maker_Helper();
					$meta_args = $query->get( 'meta_query' );
					$start = ! empty( $_GET['event_start_date'] ) ? sanitize_text_field( $_GET['event_start_date'] ) : '';
					$end = ! empty( $_GET['event_end_date'] ) ? sanitize_text_field( $_GET['event_end_date'] ) : '';
					$sb = $em_helper->is_valid_date( $start );
					$eb = $em_helper->is_valid_date( $end );

					if ( $sb === true && $eb === true ) {
						$meta_args = array(
							array(
								'key'		 => '_event_start_date',
								'value'		 => $start,
								'compare'	 => '>=',
								'type'		 => 'DATE'
							),
							array(
								'key'		 => '_event_end_date',
								'value'		 => $end,
								'compare'	 => '<=',
								'type'		 => 'DATE'
							)
						);
					} elseif ( $sb === true && $eb !== true ) {
						$meta_args = array(
							array(
								'key'		 => '_event_start_date',
								'value'		 => $start,
								'compare'	 => '>=',
								'type'		 => 'DATE'
							)
						);
					} elseif ( $sb !== true && $eb === true ) {
						$meta_args = array(
							array(
								'key'		 => '_event_end_date',
								'value'		 => $end,
								'compare'	 => '<=',
								'type'		 => 'DATE'
							)
						);
					}

					if ( ! empty( $meta_args ) )
						$query->set( 'meta_query', $meta_args );
				}
			}
		}
	}

	/**
	 * Regiseter query vars.
	 */
	public function register_query_vars( $query_vars ) {
		$query_vars[] = 'event_start_after';
		$query_vars[] = 'event_start_before';
		$query_vars[] = 'event_end_after';
		$query_vars[] = 'event_end_before';
		$query_vars[] = 'event_date_range';
		$query_vars[] = 'event_date_type';
		$query_vars[] = 'event_ticket_type';
		$query_vars[] = 'event_ondate';
		$query_vars[] = 'event_show_past_events';
		$query_vars[] = 'event_show_featured';
		$query_vars[] = 'event_show_occurrences';

		return $query_vars;
	}

	/**
	 * Extend orderby parameter.
	 */
	public function posts_orderby( $orderby, $query ) {
		if ( ! empty( $query->event_details ) )
			$orderby = 'events_meta.meta_value ' . $query->query_vars['order'] . ', ' . $orderby;

		return $orderby;
	}

	/**
	 * Extend query where parameter.
	 */
	public function posts_where( $where, $query ) {
		if ( ! empty( $query->event_details ) )
			$where .= " AND (events_meta.meta_key = '_event_occurrence_date' AND " . implode( ' AND ', $query->event_details ) . ")";

		return $where;
	}

	/**
	 * Extend query join parameter.
	 */
	public function posts_join( $join, $query ) {
		global $wpdb;

		// show occurrences?
		if (
			(is_admin() && ( ! defined( 'DOING_AJAX' ) || (defined( 'DOING_AJAX' ) && ! DOING_AJAX))) ||
			( ! isset( $query->query_vars['event_show_occurrences'] ) || (isset( $query->query_vars['event_show_occurrences'] ) && ! $query->query_vars['event_show_occurrences']))
		)
			return $join;

		/*
		  // is join really empty?
		  if(trim($join) === '')
		  $join = 'INNER JOIN '.$wpdb->postmeta.' ON ('.$wpdb->prefix.'posts.ID = '.$wpdb->postmeta.'.post_id)';
		  // just in case
		  elseif(strpos($join, 'JOIN '.$wpdb->postmeta.' ON') === false)
		  $join = 'INNER JOIN '.$wpdb->postmeta.' ON ('.$wpdb->prefix.'posts.ID = '.$wpdb->postmeta.'.post_id) '.$join;
		 */

		if ( ! empty( $query->event_details ) )
			$join .= ' INNER JOIN ' . $wpdb->postmeta . ' AS events_meta ON (' . $wpdb->prefix . 'posts.ID = events_meta.post_id)';

		return $join;
	}

	/**
	 * Extend query groupby parameter.
	 */
	public function posts_groupby( $groupby, $query ) {
		global $wpdb;

		// show occurrences?
		if (
			(is_admin() && ( ! defined( 'DOING_AJAX' ) || (defined( 'DOING_AJAX' ) && ! DOING_AJAX))) ||
			( ! isset( $query->query_vars['event_show_occurrences'] ) || (isset( $query->query_vars['event_show_occurrences'] ) && ! $query->query_vars['event_show_occurrences'])) ||
			$query->is_single === true
		)
			return $groupby;

		if ( ! empty( $query->event_details ) )
			$groupby = 'events_meta.meta_id' . (trim( $groupby ) !== '' ? ', ' . $groupby : '');
		else
			$groupby = $wpdb->postmeta . '.meta_id' . (trim( $groupby ) !== '' ? ', ' . $groupby : '');

		return $groupby;
	}

	/**
	 * Extend query to use single key to get two values.
	 */
	public function posts_fields( $select, $query ) {
		global $wpdb;

		// @TODO $fields = $query->get('fields'); $fields !== 'ids' && $fields !== 'id=>parent'
		// show occurrences?
		if (
			(is_admin() && ( ! defined( 'DOING_AJAX' ) || (defined( 'DOING_AJAX' ) && ! DOING_AJAX))) ||
			( ! isset( $query->query_vars['event_show_occurrences'] ) || (isset( $query->query_vars['event_show_occurrences'] ) && ! $query->query_vars['event_show_occurrences']))
		)
			return $select;

		if ( ! empty( $query->event_details ) )
			$select .= ", SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS event_occurrence_start_date, SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS event_occurrence_end_date";
		elseif ( ! is_single() )
			$select .= ", SUBSTRING_INDEX(" . $wpdb->postmeta . ".meta_value, '|', 1) AS event_occurrence_start_date, SUBSTRING_INDEX(" . $wpdb->postmeta . ".meta_value, '|', -1) AS event_occurrence_end_date";

		return $select;
	}

	/**
	 * Hook into pre_get_posts to do the main events query.
	 *
	 * @param 	mixed $query object
	 * @return 	void
	 */
	public function pre_get_posts( $query ) {
		// query adjustments
		if ( (is_tax( 'event-location' ) && isset( $query->query_vars['event-location'], $query->query['event-location'] )) || (is_tax( 'event-organizer' ) && isset( $query->query_vars['event-organizer'], $query->query['event-organizer'] )) || (is_tax( 'event-category' ) && isset( $query->query_vars['event-category'], $query->query['event-category'] )) ) {
			if ( ! isset( $query->query_vars['event_show_occurrences'] ) )
				$query->query_vars['event_show_occurrences'] = (is_admin() ? false : Events_Maker()->options['general']['show_occurrences']);

			if ( $query->query_vars['event_show_occurrences'] )
				$keys = array( 'start' => '_event_occurrence_date', 'end' => '_event_occurrence_date' );
			else
				$keys = array( 'start' => '_event_start_date', 'end' => '_event_end_date' );

			$event_order_by = true;

			if ( isset( $query->query_vars['orderby'] ) ) {
				if ( $query->query_vars['orderby'] === 'event_start_date' ) {
					$query->query_vars['meta_key'] = $keys['start'];
					$query->query_vars['orderby'] = 'meta_value';
				} elseif ( $query->query_vars['orderby'] === 'event_end_date' ) {
					$query->query_vars['meta_key'] = $keys['end'];
					$query->query_vars['orderby'] = 'meta_value';
				} else
					$event_order_by = false;
			}
			else {
				if ( in_array( Events_Maker()->options['general']['order_by'], array( 'start', 'end' ), true ) ) {
					$query->query_vars['meta_key'] = $keys[Events_Maker()->options['general']['order_by']];
					$query->query_vars['orderby'] = 'meta_value';
				} elseif ( Events_Maker()->options['general']['order_by'] === 'publish' ) {
					$query->query_vars['orderby'] = 'date';
					$event_order_by = false;
				} else
					$event_order_by = false;
			}

			if ( ! isset( $query->query_vars['order'] ) )
				$query->query_vars['order'] = Events_Maker()->options['general']['order'];

			if ( ! isset( $query->query_vars['event_show_past_events'] ) || ! is_bool( $query->query_vars['event_show_past_events'] ) )
				$query->query_vars['event_show_past_events'] = (is_admin() ? true : Events_Maker()->options['general']['show_past_events']);

			// some ninja fixes
			if ( $query->query_vars['event_show_occurrences'] && $query->query_vars['event_show_past_events'] && ! $event_order_by )
				$query->query_vars['meta_key'] = $keys['end'];
			elseif ( $query->query_vars['event_show_occurrences'] && ! $query->query_vars['event_show_past_events'] && $event_order_by )
				$query->query_vars['meta_key'] = '';

			$meta_args = $query->get( 'meta_query' );

			if ( $query->query_vars['event_show_occurrences'] ) {
				global $wpdb;

				$sql = array();

				if ( ! $query->query_vars['event_show_past_events'] && ! $query->is_singular )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) >= '" . current_time( 'mysql' ) . "'";

				$query->event_details = $sql;
			}
			else {
				if ( ! $query->query_vars['event_show_past_events'] && ! $query->is_singular ) {
					$meta_args[] = array(
						'key'		 => ( ! Events_Maker()->options['general']['expire_current'] ? $keys['end'] : $keys['start']),
						'value'		 => current_time( 'mysql' ),
						'compare'	 => '>=',
						'type'		 => 'DATETIME'
					);
				}
			}

			$query->set( 'meta_query', $meta_args );
		}

		$post_types = $query->get( 'post_type' );

		// does query contain post type as a string or post types array
		if ( is_array( $post_types ) ) {
			// check if there are defferrnces between the arrays
			if ( (bool) array_diff( $post_types, apply_filters( 'em_event_post_type', array( 'event' ) ) ) )
			// at least one of the post_types is not an event post type, don't run the query
				$run_query = false;
			else
			// all the post type are of event post type
				$run_query = true;
		} else
			$run_query = (bool) in_array( $post_types, apply_filters( 'em_event_post_type', array( 'event' ) ) );

		if ( $run_query ) {
			$em_helper = new Events_Maker_Helper();
			$format_sa = $format_sb = $format_ea = $format_eb = '';

			$defaults = array(
				'event_start_after'		 => '',
				'event_start_before'	 => '',
				'event_end_after'		 => '',
				'event_end_before'		 => '',
				'event_date_range'		 => 'between',
				'event_date_type'		 => 'all',
				'event_ticket_type'		 => 'all',
				'event_ondate'			 => '',
				'event_show_past_events' => (is_admin() ? true : Events_Maker()->options['general']['show_past_events']),
				'event_show_occurrences' => (is_admin() ? true : Events_Maker()->options['general']['show_occurrences'])
			);

			if ( ! empty( $query->query_vars['event_ondate'] ) ) {
				if ( get_option( 'permalink_structure' ) )
					$date = explode( '/', $query->query_vars['event_ondate'] );
				else
					$date = explode( '-', $query->query_vars['event_ondate'] );

				// year
				if ( ($a = count( $date )) === 1 ) {
					$ondate_start = $date[0] . '-01-01';
					$ondate_end = $date[0] . '-12-31';
				}
				// year + month
				elseif ( $a === 2 ) {
					$ondate_start = $date[0] . '-' . $date[1] . '-01';
					$ondate_end = $date[0] . '-' . $date[1] . '-' . date( 't', strtotime( $date[0] . '-' . $date[1] . '-02' ) );
				}
				// year + month + day
				elseif ( $a === 3 )
					$ondate_start = $ondate_end = $date[0] . '-' . $date[1] . '-' . $date[2];

				$query->set( 'event_start_before', $ondate_end );
				$query->set( 'event_end_after', $ondate_start );
			} else
				$query->query_vars['event_ondate'] = $defaults['event_ondate'];

			if ( ! isset( $query->query_vars['event_date_range'] ) || ! in_array( $query->query_vars['event_date_range'], array( 'between', 'outside' ), true ) )
				$query->query_vars['event_date_range'] = $defaults['event_date_range'];

			if ( ! isset( $query->query_vars['event_date_type'] ) || ! in_array( $query->query_vars['event_date_type'], array( 'all', 'all_day', 'not_all_day' ), true ) )
				$query->query_vars['event_date_type'] = $defaults['event_date_type'];

			if ( ! isset( $query->query_vars['event_ticket_type'] ) || ! in_array( $query->query_vars['event_ticket_type'], array( 'all', 'free', 'paid' ), true ) )
				$query->query_vars['event_ticket_type'] = $defaults['event_ticket_type'];

			if ( ! isset( $query->query_vars['event_show_past_events'] ) || ! is_bool( $query->query_vars['event_show_past_events'] ) )
				$query->query_vars['event_show_past_events'] = $defaults['event_show_past_events'];

			if ( ! isset( $query->query_vars['event_start_after'] ) || ! ($format_sa = $em_helper->is_valid_datetime( $query->query_vars['event_start_after'] )) )
				$query->query_vars['event_start_after'] = $defaults['event_start_after'];

			if ( ! isset( $query->query_vars['event_start_before'] ) || ! ($format_sb = $em_helper->is_valid_datetime( $query->query_vars['event_start_before'] )) )
				$query->query_vars['event_start_before'] = $defaults['event_start_before'];

			if ( ! isset( $query->query_vars['event_end_after'] ) || ! ($format_ea = $em_helper->is_valid_datetime( $query->query_vars['event_end_after'] )) )
				$query->query_vars['event_end_after'] = $defaults['event_end_after'];

			if ( ! isset( $query->query_vars['event_end_before'] ) || ! ($format_eb = $em_helper->is_valid_datetime( $query->query_vars['event_end_before'] )) )
				$query->query_vars['event_end_before'] = $defaults['event_end_before'];

			if ( ! isset( $query->query_vars['event_show_occurrences'] ) )
				$query->query_vars['event_show_occurrences'] = (is_admin() ? false : $defaults['event_show_occurrences']);

			if ( $query->query_vars['event_show_occurrences'] )
				$keys = array( 'start' => '_event_occurrence_date', 'end' => '_event_occurrence_date' );
			else
				$keys = array( 'start' => '_event_start_date', 'end' => '_event_end_date' );

			$event_order_by = true;
			$meta_args = $query->get( 'meta_query' );

			if ( isset( $query->query_vars['orderby'] ) ) {
				if ( $query->query_vars['orderby'] === 'event_start_date' ) {
					$query->query_vars['meta_key'] = $keys['start'];
					$query->query_vars['orderby'] = 'meta_value';
					// required to create alias of the meta table in the sql query
					/*
					$meta_args[] = array(
						'key' => $keys['start']
					);
					*/ 
				} elseif ( $query->query_vars['orderby'] === 'event_end_date' ) {
					$query->query_vars['meta_key'] = $keys['end'];
					$query->query_vars['orderby'] = 'meta_value';
					/*
					$meta_args[] = array(
						'key' => $keys['end']
					);
					*/
				} else
					$event_order_by = false;
			}
			else {
				if ( in_array( Events_Maker()->options['general']['order_by'], array( 'start', 'end' ), true ) ) {
					$query->query_vars['meta_key'] = $keys[Events_Maker()->options['general']['order_by']];
					$query->query_vars['orderby'] = 'meta_value';
					/*
					$meta_args[] = array(
						'key' => $keys[Events_Maker()->options['general']['order_by']]
					);
					*/
				} elseif ( Events_Maker()->options['general']['order_by'] === 'publish' ) {
					$query->query_vars['orderby'] = 'date';
					$event_order_by = false;
				} else
					$event_order_by = false;
			}

			if ( ! isset( $query->query_vars['order'] ) )
				$query->query_vars['order'] = Events_Maker()->options['general']['order'];

			// this must be the second meta query in sql, in order to sort by 2 meta keys at once
			if ( isset( $query->query_vars['event_show_featured'] ) && (bool) $query->query_vars['event_show_featured'] === true ) {
				$meta_args[] = array(
					'key'		 => '_event_featured',
					'value'		 => 1,
					'compare'	 => '=',
					'type'		 => 'BINARY'
				);
			}

			// some ninja fixes
			if ( $query->query_vars['event_show_occurrences'] && $query->query_vars['event_show_past_events'] && ! $event_order_by )
				$query->query_vars['meta_key'] = $keys['end'];
			elseif ( $query->query_vars['event_show_occurrences'] && ! $query->query_vars['event_show_past_events'] && $event_order_by )
				$query->query_vars['meta_key'] = '';

			if ( $format_sa === 'Y-m-d' )
				$sa_date = ($query->query_vars['event_date_range'] === 'between' ? ' 00:00:00' : ' 23:59:00');
			elseif ( $format_sa === 'Y-m-d H:i' )
				$sa_date = ':00';
			else
				$sa_date = '';

			if ( $format_sb === 'Y-m-d' )
				$sb_date = ($query->query_vars['event_date_range'] === 'between' ? ' 23:59:00' : ' 00:00:00');
			elseif ( $format_sb === 'Y-m-d H:i' )
				$sb_date = ':00';
			else
				$sb_date = '';

			if ( $format_ea === 'Y-m-d' )
				$ea_date = ($query->query_vars['event_date_range'] === 'between' ? ' 00:00:00' : ' 23:59:00');
			elseif ( $format_ea === 'Y-m-d H:i' )
				$ea_date = ':00';
			else
				$ea_date = '';

			if ( $format_eb === 'Y-m-d' )
				$eb_date = ($query->query_vars['event_date_range'] === 'between' ? ' 23:59:00' : ' 00:00:00');
			elseif ( $format_eb === 'Y-m-d H:i' )
				$eb_date = ':00';
			else
				$eb_date = '';

			if ( $query->query_vars['event_show_occurrences'] ) {
				global $wpdb;

				$sql = array();

				if ( ! empty( $query->query_vars['event_start_after'] ) )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS DATETIME) " . ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<=') . " '" . date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_start_after'] . $sa_date ) ) . "'";

				if ( ! empty( $query->query_vars['event_start_before'] ) )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS DATETIME) " . ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>=') . " '" . date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_start_before'] . $sb_date ) ) . "'";

				if ( ! empty( $query->query_vars['event_end_after'] ) )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) " . ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<=') . " '" . date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_end_after'] . $ea_date ) ) . "'";

				if ( ! empty( $query->query_vars['event_end_before'] ) )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) " . ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>=') . " '" . date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_end_before'] . $eb_date ) ) . "'";

				if ( ! $query->query_vars['event_show_past_events'] && ! $query->is_singular )
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) >= '" . current_time( 'mysql' ) . "'";

				$query->event_details = $sql;
			}
			else {
				if ( ! empty( $query->query_vars['event_start_after'] ) ) {
					$meta_args[] = array(
						'key'		 => $keys['start'],
						'value'		 => date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_start_after'] . $sa_date ) ),
						'compare'	 => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
						'type'		 => 'DATETIME'
					);
				}

				if ( ! empty( $query->query_vars['event_start_before'] ) ) {
					$meta_args[] = array(
						'key'		 => $keys['start'],
						'value'		 => date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_start_before'] . $sb_date ) ),
						'compare'	 => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
						'type'		 => 'DATETIME'
					);
				}

				if ( ! empty( $query->query_vars['event_end_after'] ) ) {
					$meta_args[] = array(
						'key'		 => $keys['end'],
						'value'		 => date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_end_after'] . $ea_date ) ),
						'compare'	 => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
						'type'		 => 'DATETIME'
					);
				}

				if ( ! empty( $query->query_vars['event_end_before'] ) ) {
					$meta_args[] = array(
						'key'		 => $keys['end'],
						'value'		 => date( 'Y-m-d H:i:s', strtotime( $query->query_vars['event_end_before'] . $eb_date ) ),
						'compare'	 => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
						'type'		 => 'DATETIME'
					);
				}

				if ( ! $query->query_vars['event_show_past_events'] && ! $query->is_singular ) {
					$meta_args[] = array(
						'key'		 => ( ! Events_Maker()->options['general']['expire_current'] ? $keys['end'] : $keys['start']),
						'value'		 => current_time( 'mysql' ),
						'compare'	 => '>=',
						'type'		 => 'DATETIME'
					);
				}
			}

			if ( $query->query_vars['event_date_type'] === 'all_day' ) {
				$meta_args[] = array(
					'key'		 => '_event_all_day',
					'value'		 => 1,
					'compare'	 => '=',
					'type'		 => 'NUMERIC'
				);
			} elseif ( $query->query_vars['event_date_type'] === 'not_all_day' ) {
				$meta_args[] = array(
					'key'		 => '_event_all_day',
					'value'		 => 0,
					'compare'	 => '=',
					'type'		 => 'NUMERIC'
				);
			}

			if ( $query->query_vars['event_ticket_type'] !== 'all' ) {
				$meta_args[] = array(
					'key'		 => '_event_free',
					'value'		 => ($query->query_vars['event_ticket_type'] === 'free' ? 1 : 0),
					'compare'	 => '=',
					'type'		 => 'NUMERIC'
				);
			}

			$query->set( 'meta_query', $meta_args );
		}
	}

	/**
	 * Extend RSS feed with event post type.
	 */
	public function feed_request( $feeds ) {
		if ( isset( $feeds['feed'] ) && ! isset( $feeds['post_type'] ) && Events_Maker()->options['general']['events_in_rss'] === true )
			$feeds['post_type'] = array( 'post', 'event' );

		return $feeds;
	}

	/**
	 * Fix for events page rules if permalinks are disabled, unfortunatelly not too elegant.
	 */
	public function alter_event_page_request( $request ) {
		if ( ! is_admin() && ! get_option( 'permalink_structure' ) && isset( $request['page_id'] ) ) {
			
			$is_event_archive = false;
			
			// WPML & Polylang
			if ( function_exists( 'icl_object_id' ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
				if ( (int) $request['page_id'] === (int) icl_object_id( Events_Maker()->options['general']['pages']['events']['id'], 'page', true, ICL_LANGUAGE_CODE ) ) {
					$is_event_archive = true;
				}
			} elseif ( (int) $request['page_id'] === (int) Events_Maker()->options['general']['pages']['events']['id'] ) {
				$is_event_archive = true;
			}
			
			// is requested page an event archive page?
			if ( $is_event_archive === true ) {
				// the query isn't run if we don't pass any query vars
				$query = new WP_Query();
				$query->parse_query( $request );
	
				if ( $query->is_page() ) {
					unset( $request['page_id'] );
					$request['post_type'] = 'event';
				}
			}
		}

		return $request;
	}

	/**
	 * Put sticky events on top of events list.
	 */
	public function sticky_featured( $posts ) {
		$post_types = apply_filters( 'em_event_post_type', array( 'event' ) );

		// apply the magic on events archive only
		if ( ! is_admin() && is_main_query() && (is_post_type_archive( $post_types ) || is_tax( array( 'event-category', 'event-tag', 'event-location', 'event-organizer' ) )) ) {
			global $wp_query;

			$args = array(
				'post_type'			 => $post_types,
				'meta_query'		 => array(
					array(
						'key'		 => '_event_featured',
						'value'		 => 1,
						'compare'	 => '='
					),
				),
				'cache_results'		 => false,
				'posts_per_page'	 => -1,
				'no_found_rows'		 => true,
				'suppress_filters'	 => true
			);
			$sticky_posts = get_posts( $args );

			if ( ! empty( $sticky_posts ) ) {
				$num_posts = count( $posts );
				$sticky_offset = 0;

				// get sticky posts ids
				foreach ( $sticky_posts as $index => $value ) {
					$sticky_ids[] = $value->ID;
				}

				// loop through the post array and find the sticky post
				for ( $i = 0; $i < $num_posts; $i ++  ) {

					// put sticky posts at the top of the posts array
					if ( in_array( $posts[$i]->ID, $sticky_ids ) ) {
						$sticky_post = $posts[$i];

						// remove sticky from current position
						array_splice( $posts, $i, 1 );

						// move to front, after other stickies
						array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
						$sticky_offset ++;

						// remove post from sticky posts array
						foreach ( $sticky_posts as $index => $value ) {
							if ( $value->ID == $sticky_post->ID ) {
								unset( $sticky_posts[$index] );
							}
						}
					}
				}

				// fetch sticky posts that weren't in the query results
				if ( ! empty( $sticky_posts ) ) {
					foreach ( $sticky_posts as $sticky_post ) {
						array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
						$sticky_offset ++;
					}
				}
			}
		}

		return $posts;
	}

	/**
	 * Modify the query according to orderby value.
	 */
	public function alter_orderby_query( $query ) {
		if ( empty( $query ) || is_admin() || ! $query->is_main_query() )
			return $query;

		$post_types = $query->get( 'post_type' );

		// does query contain post type as a string or post types array
		if ( is_array( $post_types ) ) {
			// check if there are defferrnces between the arrays
			if ( (bool) array_diff( $post_types, apply_filters( 'em_event_post_type', array( 'event' ) ) ) )
			// at least one of the post_types is not an event post type, don't run the query
				$run_query = false;
			else
			// all the post type are of event post type
				$run_query = true;
		} else
			$run_query = (bool) in_array( $post_types, apply_filters( 'em_event_post_type', array( 'event' ) ) );

		// do sorting or not
		if ( ! $run_query )
			return $query;

		// current orderby value
		$orderby_value = apply_filters( 'em_default_orderby', isset( $_GET['orderby'] ) ? esc_attr( $_GET['orderby'] ) : '' );

		if ( empty( $orderby_value ) )
			return $query;

		// get ordeby and order strings
		$orderby_value = explode( '-', $orderby_value );
		$orderby = esc_attr( $orderby_value[0] );
		$order = ! empty( $orderby_value[1] ) ? $orderby_value[1] : $order;

		$orderby = strtolower( $orderby );
		$order = strtoupper( $order );

		$args = array();

		// set orderby
		switch ( $orderby ) {
			case 'start' :
				$args['orderby'] = 'event_start_date';
				break;

			case 'end' :
				$args['orderby'] = 'event_end_date';
				break;

			case 'title' :
				$args['orderby'] = 'title';
				break;

			case 'comment_count' :
				$args['orderby'] = 'comment_count';
				break;
		}
		// set order
		$args['order'] = $order == 'ASC' ? 'ASC' : 'DESC';

		$args = apply_filters( 'em_alter_orderby_query_args', $args );

		if ( ! empty( $args['orderby'] ) )
			$query->set( 'orderby', $args['orderby'] );

		if ( ! empty( $args['order'] ) )
			$query->set( 'order', $args['order'] );

		return $query;
	}

}
