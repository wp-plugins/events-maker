<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Post_Types();

/**
 * Events_Maker_Post_Types Class.
 */
class Events_Maker_Post_Types {

	public function __construct() {
		// set instance
		Events_Maker()->post_types = $this;

		//actions
		add_action( 'init', array( &$this, 'register_taxonomies' ) );
		add_action( 'init', array( &$this, 'register_post_types' ) );
		add_action( 'admin_footer', array( &$this, 'edit_screen_icon' ) );

		//filters
		add_filter( 'post_updated_messages', array( &$this, 'register_post_types_messages' ) );
		add_filter( 'page_css_class', array( &$this, 'page_menu_class_fix' ), 10, 2 );
		add_filter( 'nav_menu_css_class', array( &$this, 'nav_menu_class_fix' ), 10, 2 );
	}

	/**
	 * Register new custom taxonomies: event-category, event-tag, event-location, event-organizer.
	 */
	public function register_taxonomies() {
		$post_types = apply_filters( 'em_event_post_type', array( 'event' ) );

		$labels_event_categories = array(
			'name'				 => _x( 'Event Categories', 'taxonomy general name', 'events-maker' ),
			'singular_name'		 => _x( 'Event Category', 'taxonomy singular name', 'events-maker' ),
			'search_items'		 => __( 'Search Event Categories', 'events-maker' ),
			'all_items'			 => __( 'All Event Categories', 'events-maker' ),
			'parent_item'		 => __( 'Parent Event Category', 'events-maker' ),
			'parent_item_colon'	 => __( 'Parent Event Category:', 'events-maker' ),
			'edit_item'			 => __( 'Edit Event Category', 'events-maker' ),
			'view_item'			 => __( 'View Event Category', 'events-maker' ),
			'update_item'		 => __( 'Update Event Category', 'events-maker' ),
			'add_new_item'		 => __( 'Add New Event Category', 'events-maker' ),
			'new_item_name'		 => __( 'New Event Category Name', 'events-maker' ),
			'menu_name'			 => __( 'Categories', 'events-maker' ),
		);

		$labels_event_locations = array(
			'name'				 => _x( 'Locations', 'taxonomy general name', 'events-maker' ),
			'singular_name'		 => _x( 'Event Location', 'taxonomy singular name', 'events-maker' ),
			'search_items'		 => __( 'Search Event Locations', 'events-maker' ),
			'all_items'			 => __( 'All Event Locations', 'events-maker' ),
			'parent_item'		 => __( 'Parent Event Location', 'events-maker' ),
			'parent_item_colon'	 => __( 'Parent Event Location:', 'events-maker' ),
			'edit_item'			 => __( 'Edit Event Location', 'events-maker' ),
			'view_item'			 => __( 'View Event Location', 'events-maker' ),
			'update_item'		 => __( 'Update Event Location', 'events-maker' ),
			'add_new_item'		 => __( 'Add New Event Location', 'events-maker' ),
			'new_item_name'		 => __( 'New Event Location Name', 'events-maker' ),
			'menu_name'			 => __( 'Locations', 'events-maker' ),
		);

		$args_event_categories = array(
			'public'				 => true,
			'hierarchical'			 => true,
			'labels'				 => $labels_event_categories,
			'show_ui'				 => true,
			'show_admin_column'		 => true,
			'update_count_callback'	 => '_update_post_term_count',
			'query_var'				 => true,
			'rewrite'				 => array(
				'slug'			 => Events_Maker()->options['permalinks']['event_rewrite_base'] . '/' . Events_Maker()->options['permalinks']['event_categories_rewrite_slug'],
				'with_front'	 => false,
				'hierarchical'	 => true
			),
			'capabilities'			 => array(
				'manage_terms'	 => 'manage_event_categories',
				'edit_terms'	 => 'manage_event_categories',
				'delete_terms'	 => 'manage_event_categories',
				'assign_terms'	 => 'edit_events'
			)
		);

		$args_event_locations = array(
			'public'				 => true,
			'hierarchical'			 => true,
			'labels'				 => $labels_event_locations,
			'show_ui'				 => true,
			'show_admin_column'		 => true,
			'update_count_callback'	 => '_update_post_term_count',
			'query_var'				 => true,
			'rewrite'				 => array(
				'slug'			 => Events_Maker()->options['permalinks']['event_rewrite_base'] . '/' . Events_Maker()->options['permalinks']['event_locations_rewrite_slug'],
				'with_front'	 => false,
				'hierarchical'	 => false
			),
			'capabilities'			 => array(
				'manage_terms'	 => 'manage_event_locations',
				'edit_terms'	 => 'manage_event_locations',
				'delete_terms'	 => 'manage_event_locations',
				'assign_terms'	 => 'edit_events'
			)
		);

		register_taxonomy( 'event-category', apply_filters( 'em_register_event_categories_for', $post_types ), apply_filters( 'em_register_event_categories', $args_event_categories ) );

		if ( Events_Maker()->options['general']['use_tags'] ) {
			$labels_event_tags = array(
				'name'						 => _x( 'Event Tags', 'taxonomy general name', 'events-maker' ),
				'singular_name'				 => _x( 'Event Tag', 'taxonomy singular name', 'events-maker' ),
				'search_items'				 => __( 'Search Event Tags', 'events-maker' ),
				'popular_items'				 => __( 'Popular Event Tags', 'events-maker' ),
				'all_items'					 => __( 'All Event Tags', 'events-maker' ),
				'parent_item'				 => null,
				'parent_item_colon'			 => null,
				'edit_item'					 => __( 'Edit Event Tag', 'events-maker' ),
				'update_item'				 => __( 'Update Event Tag', 'events-maker' ),
				'add_new_item'				 => __( 'Add New Event Tag', 'events-maker' ),
				'new_item_name'				 => __( 'New Event Tag Name', 'events-maker' ),
				'separate_items_with_commas' => __( 'Separate event tags with commas', 'events-maker' ),
				'add_or_remove_items'		 => __( 'Add or remove event tags', 'events-maker' ),
				'choose_from_most_used'		 => __( 'Choose from the most used event tags', 'events-maker' ),
				'menu_name'					 => __( 'Tags', 'events-maker' ),
			);

			$args_event_tags = array(
				'public'				 => true,
				'hierarchical'			 => false,
				'labels'				 => $labels_event_tags,
				'show_ui'				 => true,
				'show_admin_column'		 => true,
				'update_count_callback'	 => '_update_post_term_count',
				'query_var'				 => true,
				'rewrite'				 => array(
					'slug'			 => Events_Maker()->options['permalinks']['event_rewrite_base'] . '/' . Events_Maker()->options['permalinks']['event_tags_rewrite_slug'],
					'with_front'	 => false,
					'hierarchical'	 => false
				),
				'capabilities'			 => array(
					'manage_terms'	 => 'manage_event_tags',
					'edit_terms'	 => 'manage_event_tags',
					'delete_terms'	 => 'manage_event_tags',
					'assign_terms'	 => 'edit_events'
				)
			);

			register_taxonomy( 'event-tag', apply_filters( 'em_register_event_tags_for', $post_types ), apply_filters( 'em_register_event_tags', $args_event_tags ) );
		}

		register_taxonomy( 'event-location', apply_filters( 'em_register_event_locations_for', $post_types ), apply_filters( 'em_register_event_locations', $args_event_locations ) );

		if ( Events_Maker()->options['general']['use_organizers'] ) {
			$labels_event_organizers = array(
				'name'				 => _x( 'Organizers', 'taxonomy general name', 'events-maker' ),
				'singular_name'		 => _x( 'Event Organizer', 'taxonomy singular name', 'events-maker' ),
				'search_items'		 => __( 'Search Event Organizers', 'events-maker' ),
				'all_items'			 => __( 'All Event Organizers', 'events-maker' ),
				'parent_item'		 => __( 'Parent Event Organizer', 'events-maker' ),
				'parent_item_colon'	 => __( 'Parent Event Organizer:', 'events-maker' ),
				'edit_item'			 => __( 'Edit Event Organizer', 'events-maker' ),
				'view_item'			 => __( 'View Event Organizer', 'events-maker' ),
				'update_item'		 => __( 'Update Event Organizer', 'events-maker' ),
				'add_new_item'		 => __( 'Add New Event Organizer', 'events-maker' ),
				'new_item_name'		 => __( 'New Event Organizer Name', 'events-maker' ),
				'menu_name'			 => __( 'Organizers', 'events-maker' ),
			);

			$args_event_organizers = array(
				'public'				 => true,
				'hierarchical'			 => true,
				'labels'				 => $labels_event_organizers,
				'show_ui'				 => true,
				'show_admin_column'		 => true,
				'update_count_callback'	 => '_update_post_term_count',
				'query_var'				 => true,
				'rewrite'				 => array(
					'slug'			 => Events_Maker()->options['permalinks']['event_rewrite_base'] . '/' . Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'],
					'with_front'	 => false,
					'hierarchical'	 => false
				),
				'capabilities'			 => array(
					'manage_terms'	 => 'manage_event_organizers',
					'edit_terms'	 => 'manage_event_organizers',
					'delete_terms'	 => 'manage_event_organizers',
					'assign_terms'	 => 'edit_events'
				)
			);

			register_taxonomy( 'event-organizer', apply_filters( 'em_register_event_organizers_for', $post_types ), apply_filters( 'em_register_event_organizers', $args_event_organizers ) );
		}
	}

	/**
	 * Register new post types: event.
	 */
	public function register_post_types() {
		$labels_event = array(
			'name'				 => _x( 'Events', 'post type general name', 'events-maker' ),
			'singular_name'		 => _x( 'Event', 'post type singular name', 'events-maker' ),
			'menu_name'			 => __( 'Events', 'events-maker' ),
			'all_items'			 => __( 'All Events', 'events-maker' ),
			'add_new'			 => __( 'Add New', 'events-maker' ),
			'add_new_item'		 => __( 'Add New Event', 'events-maker' ),
			'edit_item'			 => __( 'Edit Event', 'events-maker' ),
			'new_item'			 => __( 'New Event', 'events-maker' ),
			'view_item'			 => __( 'View Event', 'events-maker' ),
			'items_archive'		 => __( 'Event Archive', 'events-maker' ),
			'search_items'		 => __( 'Search Event', 'events-maker' ),
			'not_found'			 => __( 'No events found', 'events-maker' ),
			'not_found_in_trash' => __( 'No events found in trash', 'events-maker' ),
			'parent_item_colon'	 => ''
		);

		$taxonomies = array( 'event-category', 'event-location' );

		if ( Events_Maker()->options['general']['use_tags'] )
			$taxonomies[] = 'event-tag';

		if ( Events_Maker()->options['general']['use_organizers'] )
			$taxonomies[] = 'event-organizer';

		// menu icon
		global $wp_version;

		if ( version_compare( $wp_version, '3.8', '>=' ) )
			$menu_icon = 'dashicons-calendar';
		else
			$menu_icon = EVENTS_MAKER_URL . '/images/icon-events-16.png';

		// get supported features
		$supports = array();

		foreach ( Events_Maker()->options['general']['supports'] as $support => $bool ) {
			if ( $bool )
				$supports[] = $support;
		}

		// unset gallery as this is not a post type support feature
		if ( isset( $supports['gallery'] ) )
			unset( $supports['gallery'] );

		$args_event = array(
			'labels'				 => $labels_event,
			'description'			 => '',
			'public'				 => true,
			'exclude_from_search'	 => false,
			'publicly_queryable'	 => true,
			'show_ui'				 => true,
			'show_in_menu'			 => true,
			'show_in_admin_bar'		 => true,
			'show_in_nav_menus'		 => true,
			'menu_position'			 => 5,
			'menu_icon'				 => $menu_icon,
			'capability_type'		 => 'event',
			'capabilities'			 => array(
				'publish_posts'			 => 'publish_events',
				'edit_posts'			 => 'edit_events',
				'edit_others_posts'		 => 'edit_others_events',
				'edit_published_posts'	 => 'edit_published_events',
				'delete_published_posts' => 'delete_published_events',
				'delete_posts'			 => 'delete_events',
				'delete_others_posts'	 => 'delete_others_events',
				'read_private_posts'	 => 'read_private_events',
				'edit_post'				 => 'edit_event',
				'delete_post'			 => 'delete_event',
				'read_post'				 => 'read_event',
			),
			'map_meta_cap'			 => true,
			'hierarchical'			 => false,
			'supports'				 => $supports,
			'rewrite'				 => array(
				'slug'		 => Events_Maker()->options['permalinks']['event_rewrite_base'] . '/' . Events_Maker()->options['permalinks']['event_rewrite_slug'],
				'with_front' => false,
				'feeds'		 => true,
				'pages'		 => true
			),
			'has_archive'			 => Events_Maker()->options['permalinks']['event_rewrite_base'],
			'query_var'				 => true,
			'can_export'			 => true,
			'taxonomies'			 => $taxonomies,
		);

		register_post_type( 'event', apply_filters( 'em_register_event_post_type', $args_event ) );
	}

	/**
	 * Custom post type messages.
	 */
	public function register_post_types_messages( $messages ) {
		global $post, $post_ID;

		$messages['event'] = array(
			0	 => '', //Unused. Messages start at index 1.
			1	 => sprintf( __( 'Event updated. <a href="%s">View event</a>', 'events-maker' ), esc_url( get_permalink( $post_ID ) ) ),
			2	 => __( 'Custom field updated.', 'events-maker' ),
			3	 => __( 'Custom field deleted.', 'events-maker' ),
			4	 => __( 'Event updated.', 'events-maker' ),
			//translators: %s: date and time of the revision
			5	 => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'events-maker' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6	 => sprintf( __( 'Event published. <a href="%s">View event</a>', 'events-maker' ), esc_url( get_permalink( $post_ID ) ) ),
			7	 => __( 'Event saved.', 'events-maker' ),
			8	 => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>', 'events-maker' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9	 => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'events-maker' ),
				//translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10	 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'events-maker' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) )
		);

		return $messages;
	}

	/**
	 * Edit screen icon.
	 */
	public function edit_screen_icon() {
		// screen icon
		global $wp_version;

		if ( $wp_version < 3.8 ) {
			global $post;

			$post_types = apply_filters( 'em_event_post_type', array( 'event' ) );

			foreach ( $post_types as $post_type ) {
				if ( get_post_type( $post ) === $post_type || (isset( $_GET['post_type'] ) && $_GET['post_type'] === $post_type) ) {
					echo '
					<style>
						#icon-edit { background: transparent url(\'' . EVENTS_MAKER_URL . '/images/icon-events-32.png\') no-repeat; }
					</style>';
				}
			}
		}
	}

	/**
	 * Nav menu classes fix.
	 */
	public function nav_menu_class_fix( $classes, $item ) {
		if ( is_post_type_archive( 'event' ) || is_singular( 'event' ) || is_tax( 'event-location' ) || is_tax( 'event-organizer' ) ) {
			if ( Events_Maker()->admin->get_action_page_id( 'events' ) > 0 ) {
				$events_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'events' ) );

				if ( empty( $events_page ) )
					return $classes;

				// events page menu classes
				if ( $events_page->post_title === $item->title ) {
					if ( is_singular( 'event' ) ) {
						$classes[] = 'current-menu-parent';
						$classes[] = 'current-menu-ancestor';
					} elseif ( is_tax( 'event-location' ) || is_tax( 'event-organizer' ) )
						$classes[] = 'current-menu-ancestor';
					else
						$classes[] = 'current-menu-item';
				}
				// location page menu classes
				if ( is_tax( 'event-location' ) && Events_Maker()->admin->get_action_page_id( 'locations' ) > 0 ) {
					$locations_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'locations' ) );

					if ( $locations_page->post_title === $item->title )
						$classes[] = 'current-menu-ancestor';
				}
				// organizer page menu classes
				elseif ( is_tax( 'event-organizer' ) && Events_Maker()->admin->get_action_page_id( 'organizers' ) > 0 ) {
					$organizers_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'organizers' ) );

					if ( $organizers_page->post_title === $item->title )
						$classes[] = 'current-menu-ancestor';
				}
			}
		}
		return $classes;
	}

	/**
	 * Page menu classes fix.
	 */
	public function page_menu_class_fix( $classes, $item ) {
		if ( is_post_type_archive( 'event' ) || is_singular( 'event' ) || is_tax( 'event-location' ) || is_tax( 'event-organizer' ) ) {
			if ( Events_Maker()->admin->get_action_page_id( 'events' ) > 0 ) {
				$events_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'events' ) );

				if ( empty( $events_page ) )
					return $classes;

				// events page menu classes
				if ( $events_page->post_title === $item->post_title ) {
					if ( is_singular( 'event' ) ) {
						$classes[] = 'current_page_parent';
						$classes[] = 'current_page_ancestor';
					} elseif ( is_tax( 'event-location' ) || is_tax( 'event-organizer' ) )
						$classes[] = 'current_page_ancestor';
					else
						$classes[] = 'current_page_item';
				}
				// location page menu classes
				if ( is_tax( 'event-location' ) && Events_Maker()->admin->get_action_page_id( 'locations' ) > 0 ) {
					$locations_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'locations' ) );

					if ( $locations_page->post_title === $item->post_title )
						$classes[] = 'current_page_ancestor';
				}
				// organizer page menu classes
				elseif ( is_tax( 'event-organizer' ) && Events_Maker()->admin->get_action_page_id( 'organizers' ) > 0 ) {
					$organizers_page = get_post( (int) Events_Maker()->admin->get_action_page_id( 'organizers' ) );

					if ( $organizers_page->post_title === $item->post_title )
						$classes[] = 'current_page_ancestor';
				}
			}
		}
		return $classes;
	}

}
