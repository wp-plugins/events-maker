<?php
/*
Plugin Name: Events Maker
Description: Fully featured event management system including recurring events, locations management, full calendar, iCal feed/files, google maps and more.
Version: 1.6.4
Author: dFactory
Author URI: http://www.dfactory.eu/
Plugin URI: http://www.dfactory.eu/plugins/events-maker/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Text Domain: events-maker
Domain Path: /languages

Events Maker
Copyright (C) 2013-2015, Digital Factory - info@digitalfactory.pl

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

 // exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

define( 'EVENTS_MAKER_URL', plugins_url( '', __FILE__ ) );
define( 'EVENTS_MAKER_PATH', plugin_dir_path( __FILE__ ) );
define( 'EVENTS_MAKER_REL_PATH', dirname( plugin_basename( __FILE__ ) ) . '/' );
define( 'EVENTS_MAKER_UPDATE_VERSION_1', '1.0.10' );

include_once( EVENTS_MAKER_PATH . 'includes/core-functions.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-admin.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-helper.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-ical.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-localisation.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-listing.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-metaboxes.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-query.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-post-types.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-shortcodes.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-taxonomies.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-templates.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-widgets.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-settings.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-update.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-welcome.php' );
include_once( EVENTS_MAKER_PATH . 'includes/class-wpml.php' );
include_once( EVENTS_MAKER_PATH . 'includes/libraries/translate-rewrite-slugs.php' );

/**
 * Events Maker class.
 *
 * @class Events_Maker
 * @version	1.6.4
 */
final class Events_Maker {

	private static $_instance;
	public $action_pages = array();
	public $notices = array();
	public $options = array();
	public $recurrences = array();
	public $defaults = array(
		'general'		 => array(
			'supports'				 => array(
				'title'			 => true,
				'editor'		 => true,
				'author'		 => true,
				'thumbnail'		 => true,
				'excerpt'		 => true,
				'custom-fields'	 => false,
				'comments'		 => true,
				'trackbacks'	 => false,
				'revisions'		 => false,
				'gallery'		 => true
			),
			'order_by'				 => 'start',
			'order'					 => 'asc',
			'expire_current'		 => false,
			'show_past_events'		 => true,
			'show_occurrences'		 => true,
			'use_organizers'		 => true,
			'use_tags'				 => true,
			'use_event_tickets'		 => true,
			'default_event_options'	 => array(
				'google_map'				 => true,
				'display_gallery'			 => true,
				'display_location_details'	 => true,
				'price_tickets_info'		 => true,
				'display_organizer_details'	 => true
			),
			'pages'					 => array(
				'events'	 => array(
					'id'		 => 0,
					'position'	 => 'after'
				),
				'calendar'	 => array(
					'id'		 => 0,
					'position'	 => 'after'
				),
				/* 'past_events' => array(
				  'id' => 0,
				  'position' => 'after'
				  ), */
				'locations'	 => array(
					'id'		 => 0,
					'position'	 => 'after'
				),
				'organizers' => array(
					'id'		 => 0,
					'position'	 => 'after'
				)
			),
			'pages_notice'			 => true,
			'ical_feed'				 => true,
			'events_in_rss'			 => true,
			'deactivation_delete'	 => false,
			'datetime_format'		 => array(
				'date'	 => '',
				'time'	 => ''
			),
			'first_weekday'			 => 1,
			'rewrite_rules'			 => true,
			'currencies'			 => array(
				'code'		 => 'usd',
				'symbol'	 => '$',
				'position'	 => 'after',
				'format'	 => 1
			)
		),
		'templates'		 => array(
			'default_templates' => true
		),
		'capabilities'	 => array(
			'publish_events',
			'edit_events',
			'edit_others_events',
			'edit_published_events',
			'delete_published_events',
			'delete_events',
			'delete_others_events',
			'read_private_events',
			'manage_event_categories',
			'manage_event_tags',
			'manage_event_locations',
			'manage_event_organizers'
		),
		'permalinks'	 => array(
			'event_rewrite_base'			 => 'events',
			'event_rewrite_slug'			 => 'event',
			'event_categories_rewrite_slug'	 => 'category',
			'event_tags_rewrite_slug'		 => 'tag',
			'event_locations_rewrite_slug'	 => 'location',
			'event_organizers_rewrite_slug'	 => 'organizer'
		),
		'version'		 => '1.6.4'
	);
	private $transient_id = '';

	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Main Events Maker instance.
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Events Maker constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( &$this, 'multisite_activation' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'multisite_deactivation' ) );

		// settings
		$this->options = array(
			'general'	 => array_merge( $this->defaults['general'], get_option( 'events_maker_general', $this->defaults['general'] ) ),
			'permalinks' => array_merge( $this->defaults['permalinks'], get_option( 'events_maker_permalinks', $this->defaults['permalinks'] ) ),
			'templates'	 => array_merge( $this->defaults['templates'], get_option( 'events_maker_templates', $this->defaults['templates'] ) )
		);

		// session id
		$this->transient_id = (isset( $_COOKIE['em_transient_id'] ) ? $_COOKIE['em_transient_id'] : 'em_' . sha1( $this->generate_hash() ));

		// actions
		add_action( 'plugins_loaded', array( &$this, 'init_session' ), 1 );
		add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'front_scripts_styles' ) );
		add_action( 'after_setup_theme', array( &$this, 'load_defaults' ) );
		add_action( 'wp', array( &$this, 'load_pluggable_functions' ) );
		add_action( 'wp', array( &$this, 'load_pluggable_hooks' ) );

		// filters
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_settings_link' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_extend_links' ), 10, 2 );

		do_action( 'em_loaded' );
	}

	/**
	 * Multisite activation.
	 */
	public function multisite_activation( $networkwide ) {
		if ( is_multisite() && $networkwide ) {
			global $wpdb;

			$activated_blogs = array();
			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT blog_id FROM ' . $wpdb->blogs, '' ) );

			foreach ( $blogs_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->activate_single();
				$activated_blogs[] = (int) $blog_id;
			}

			switch_to_blog( $current_blog_id );
			update_site_option( 'events_maker_activated_blogs', $activated_blogs, array() );
		} else
			$this->activate_single();
	}

	/**
	 * Single site activation.
	 */
	public function activate_single() {
		global $wp_roles;

		// transient for welcome screen
		set_transient( 'em_activation_redirect', 1, 3600 );

		// add caps to administrators
		foreach ( $wp_roles->roles as $role_name => $display_name ) {
			$role = $wp_roles->get_role( $role_name );

			if ( $role->has_cap( 'manage_options' ) ) {
				foreach ( $this->defaults['capabilities'] as $capability ) {
					if ( ( ! $this->defaults['general']['use_tags'] && $capability === 'manage_event_tags') || ( ! $this->defaults['general']['use_organizers'] && $capability === 'manage_event_organizers') )
						continue;

					$role->add_cap( $capability );
				}
			}
		}

		$this->defaults['general']['datetime_format'] = array(
			'date'	 => get_option( 'date_format' ),
			'time'	 => get_option( 'time_format' )
		);

		// add default options
		add_option( 'events_maker_general', $this->defaults['general'], '', 'no' );
		add_option( 'events_maker_templates', $this->defaults['templates'], '', 'no' );
		add_option( 'events_maker_capabilities', '', '', 'no' );
		add_option( 'events_maker_permalinks', $this->defaults['permalinks'], '', 'no' );
		add_option( 'events_maker_version', $this->defaults['version'], '', 'no' );

		// permalinks
		flush_rewrite_rules();
	}

	/**
	 * Multisite deactivation.
	 */
	public function multisite_deactivation( $networkwide ) {
		if ( is_multisite() && $networkwide ) {
			global $wpdb;

			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT blog_id FROM ' . $wpdb->blogs, '' ) );

			if ( ! ($activated_blogs = get_site_option( 'events_maker_activated_blogs', false, false )) )
				$activated_blogs = array();

			foreach ( $blogs_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->deactivate_single( true );

				if ( in_array( (int) $blog_id, $activated_blogs, true ) )
					unset( $activated_blogs[array_search( $blog_id, $activated_blogs )] );
			}

			switch_to_blog( $current_blog_id );
			update_site_option( 'events_maker_activated_blogs', $activated_blogs );
		} else
			$this->deactivate_single();
	}

	/**
	 * Single site deactivation.
	 */
	public function deactivate_single( $multi = false ) {
		global $wp_roles;

		// remove capabilities
		foreach ( $wp_roles->roles as $role_name => $display_name ) {
			$role = $wp_roles->get_role( $role_name );

			foreach ( $this->defaults['capabilities'] as $capability ) {
				$role->remove_cap( $capability );
			}
		}

		if ( $multi ) {
			$options = get_option( 'events_maker_general' );
			$check = $options['deactivation_delete'];
		} else {
			$check = $this->options['general']['deactivation_delete'];
		}

		// delete default options
		if ( $check ) {
			delete_option( 'events_maker_general' );
			delete_option( 'events_maker_templates' );
			delete_option( 'events_maker_capabilities' );
			delete_option( 'events_maker_permalinks' );
			delete_option( 'events_maker_version' );
		}

		// permalinks
		flush_rewrite_rules();
	}

	/**
	 * Load defaults.
	 */
	public function load_defaults() {
		$this->recurrences = apply_filters(
			'em_event_recurrences_options', array(
				'once'		 => __( 'once', 'events-maker' ),
				'daily'		 => __( 'daily', 'events-maker' ),
				'weekly'	 => __( 'weekly', 'events-maker' ),
				'monthly'	 => __( 'monthly', 'events-maker' ),
				'yearly'	 => __( 'yearly', 'events-maker' ),
				'custom'	 => __( 'custom', 'events-maker' )
			)
		);

		$this->action_pages = apply_filters( 'em_action_pages', array(
			'events'	 => __( 'Events', 'events-maker' ),
			'calendar'	 => __( 'Calendar', 'events-maker' ),
			// 'past_events' => __('Past Events', 'events-maker'),
			'locations'	 => __( 'Locations', 'events-maker' ),
			'organizers' => __( 'Organizers', 'events-maker' )
		) );
	}

	/**
	 * Load pluggable template functions.
	 */
	public function load_pluggable_functions() {
		include_once( EVENTS_MAKER_PATH . 'includes/template-functions.php' );
	}

	/**
	 * Load pluggable template hooks.
	 */
	public function load_pluggable_hooks() {
		include_once( EVENTS_MAKER_PATH . 'includes/template-hooks.php' );
	}

	/**
	 * Get session id.
	 */
	public function get_session_id() {
		return $this->transient_id;
	}

	/**
	 * Generate random string.
	 */
	private function generate_hash() {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
		$max = strlen( $chars ) - 1;
		$password = '';

		for ( $i = 0; $i < 64; $i ++  ) {
			$password .= substr( $chars, mt_rand( 0, $max ), 1 );
		}

		return $password;
	}

	/**
	 * Initialize cookie-session.
	 */
	public function init_session() {
		setcookie( 'em_transient_id', $this->transient_id, 0, COOKIEPATH, COOKIE_DOMAIN );
	}

	/**
	 * Load text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'events-maker', false, EVENTS_MAKER_REL_PATH . 'languages/' );
	}

	/**
	 * Enqueue frontend scripts and style.
	 */
	public function front_scripts_styles() {
		wp_register_style(
			'events-maker-front', EVENTS_MAKER_URL . '/css/front.css'
		);

		wp_enqueue_style( 'events-maker-front' );

		wp_register_script(
			'events-maker-sorting', EVENTS_MAKER_URL . '/js/front-sorting.js', array( 'jquery' )
		);

		wp_enqueue_script( 'events-maker-sorting' );
	}

	/**
	 * Add link to Settings page.
	 */
	public function plugin_settings_link( $links ) {
		if ( ! is_admin() || ! current_user_can( 'install_plugins' ) )
			return $links;

		$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php' ) . '?post_type=event&page=events-settings', __( 'Settings', 'events-maker' ) );

		return $links;
	}

	/**
	 * Add link to Support Forum.
	 */
	public function plugin_extend_links( $links, $file ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return $links;
		}

		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin ) {
			return array_merge( $links, array( sprintf( '<a href="http://www.dfactory.eu/support/forum/events-maker/" target="_blank">%s</a>', __( 'Support', 'events-maker' ) ) ) );
		}

		return $links;
	}

}

/**
 * Initialise Events Maker.
 */
function Events_Maker() {
	static $instance;

	// first call to instance() initializes the plugin
	if ( $instance === null || ! ( $instance instanceof Events_Maker ) ) {
		$instance = Events_Maker::instance();
	}

	return $instance;
}

Events_Maker();
?>