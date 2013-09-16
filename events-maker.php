<?php
/*
Plugin Name: Events Maker
Description: Events Maker is an easy to use but flexible events management plugin made the WordPress way.
Version: 1.0.0
Author: dFactory
Author URI: http://www.dfactory.eu/
Plugin URI: http://www.dfactory.eu/plugins/events-maker/
License: MIT License

Events Maker
Copyright (C) 2013, Digital Factory - info@digitalfactory.pl

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

define('EVENTS_MAKER_URL', plugins_url('', __FILE__));
define('EVENTS_MAKER_PATH', plugin_dir_path(__FILE__));
define('EVENTS_MAKER_REL_PATH', dirname(plugin_basename(__FILE__)).'/');

$events_maker = new Events_Maker();

include_once(EVENTS_MAKER_PATH.'includes/settings.php');
include_once(EVENTS_MAKER_PATH.'includes/query.php');
include_once(EVENTS_MAKER_PATH.'includes/taxonomies.php');
include_once(EVENTS_MAKER_PATH.'includes/templates.php');
include_once(EVENTS_MAKER_PATH.'includes/listing.php');
include_once(EVENTS_MAKER_PATH.'includes/metaboxes.php');
include_once(EVENTS_MAKER_PATH.'includes/widgets.php');
include_once(EVENTS_MAKER_PATH.'includes/functions.php');
include_once(EVENTS_MAKER_PATH.'includes/helper.php');
include_once(EVENTS_MAKER_PATH.'includes/welcome.php');

class Events_Maker
{
	private $options = array();
	private $currencies = array();
	private $defaults = array(
		'general' => array(
			'supports' => array(
				'title' => TRUE,
				'editor' => TRUE,
				'author' => TRUE,
				'thumbnail' => TRUE,
				'excerpt' => TRUE,
				'custom-fields' => FALSE,
				'comments' => TRUE,
				'trackbacks' => FALSE,
				'revisions' => FALSE
			),
			'order_by' => 'start',
			'order' => 'asc',
			'expire_current' => FALSE,
			'show_past_events' => TRUE,
			'use_organizers' => TRUE,
			'use_tags' => TRUE,
			'use_event_tickets' => TRUE,
			'deactivation_delete' => FALSE,
			'event_nav_menu' => array(
				'show' => FALSE,
				'menu_name' => '',
				'menu_id' => 0,
				'item_id' => 0
			),
			'datetime_format' => array(
				'date' => '',
				'time' => ''
			),
			'first_weekday' => 1,
			'rewrite_rules' => TRUE,
			'currencies' => array(
				'code' => 'usd',
				'symbol' => '$',
				'position' => 'after',
				'format' => 1
			)
		),
		'templates' => array(
			'default_templates' => TRUE
		),
		'capabilities' => array(
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
		'permalinks' => array(
			'event_rewrite_base' => 'events',
			'event_rewrite_slug' => 'event',
			'event_categories_rewrite_slug' => 'category',
			'event_tags_rewrite_slug' => 'tag',
			'event_locations_rewrite_slug' => 'location',
			'event_organizers_rewrite_slug' => 'organizer'
		),
		'version' => '1.0.0'
	);
	private $transient_id = '';


	public function __construct()
	{
		register_activation_hook(__FILE__, array(&$this, 'activation'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivation'));

		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general')),
			array('permalinks' => get_option('events_maker_permalinks')),
			array('templates' => get_option('events_maker_templates'))
		);

		//session id
		$this->transient_id = (isset($_COOKIE['em_transient_id']) ? $_COOKIE['em_transient_id'] : 'emtr_'.sha1($this->generate_hash()));

		//actions
		add_action('init', array(&$this, 'register_taxonomies'));
		add_action('init', array(&$this, 'register_post_types'));
		add_action('init', array(&$this, 'register_map_shortcode'));
		add_action('plugins_loaded', array(&$this, 'init_session'), 1);
		add_action('plugins_loaded', array(&$this, 'load_textdomain'));
		add_action('admin_footer', array(&$this, 'edit_screen_icon'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts_styles'));
		add_action('wp_enqueue_scripts', array(&$this, 'front_scripts_styles'));
		add_action('admin_notices', array(&$this, 'event_admin_notices'));
		add_action('after_setup_theme', array(&$this, 'pass_variables'), 9);

		//filters
		add_filter('map_meta_cap', array(&$this, 'event_map_meta_cap'), 10, 4);
		add_filter('post_updated_messages', array(&$this, 'register_post_types_messages'));
		add_filter('plugin_row_meta', array(&$this, 'plugin_extend_links'), 10, 2);
	}


	/**
	 * Passes variables (currencies) to other classes
	*/
	public function pass_variables()
	{
		$this->currencies = array(
			'codes' => array(
				'usd' => __('US Dollars (&#36;)', 'events-maker'),
				'eur' => __('Euros (&euro;)', 'events-maker'),
				'gbp' => __('Pounds Sterling (&pound;)', 'events-maker'),
				'aud' => __('Australian Dollars (&#36;)', 'events-maker'),
				'brl' => __('Brazilian Real (R&#36;)', 'events-maker'),
				'cad' => __('Canadian Dollars (&#36;)', 'events-maker'),
				'czk' => __('Czech Koruna', 'events-maker'),
				'dkk' => __('Danish Krone', 'events-maker'),
				'hkd' => __('Hong Kong Dollar (&#36;)', 'events-maker'),
				'huf' => __('Hungarian Forint', 'events-maker'),
				'ils' => __('Israeli Shekel (&#8362;)', 'events-maker'),
				'jpy' => __('Japanese Yen (&yen;)', 'events-maker'),
				'myr' => __('Malaysian Ringgits', 'events-maker'),
				'mxn' => __('Mexican Peso (&#36;)', 'events-maker'),
				'nzd' => __('New Zealand Dollar (&#36;)', 'events-maker'),
				'nok' => __('Norwegian Krone', 'events-maker'),
				'php' => __('Philippine Pesos', 'events-maker'),
				'pln' => __('Polish Zloty', 'events-maker'),
				'sgd' => __('Singapore Dollar (&#36;)', 'events-maker'),
				'sek' => __('Swedish Krona', 'events-maker'),
				'chf' => __('Swiss Franc', 'events-maker'),
				'twd' => __('Taiwan New Dollars', 'events-maker'),
				'thb' => __('Thai Baht (&#3647;)', 'events-maker'),
				'inr' => __('Indian Rupee (&#8377;)', 'events-maker'),
				'try' => __('Turkish Lira (&#8378;)', 'events-maker'),
				'rial' => __('Iranian Rial (&#65020;)', 'events-maker')
			),
			'positions' => array(
				'before' => __('before price', 'events-maker'),
				'after' => __('after price', 'events-maker')
			),
			'formats' => array(
				1 => '1,234.56',
				2 => '1,234',
				3 => '1234',
				4 => '1234.56',
				5 => '1 234,56',
				6 => '1 234.56'
			)
		);
	}


	/**
	 * Execution of plugin activation function
	*/
	public function activation()
	{
		global $wp_roles;

		// transient for welcome screen
		set_transient('_events_maker_activation_redirect', 1, 60 * 60);

		//add caps to administrators
		foreach($wp_roles->roles as $role_name => $display_name)
		{
			$role = $wp_roles->get_role($role_name);

			if($role->has_cap('manage_options'))
			{
				foreach($this->defaults['capabilities'] as $capability)
				{
					if(($this->defaults['general']['use_tags'] === FALSE && $capability === 'manage_event_tags') || ($this->defaults['general']['use_organizers'] === FALSE && $capability === 'manage_event_organizers'))
						continue;

					$role->add_cap($capability);
				}
			}
		}

		$this->defaults['general']['datetime_format'] = array(
			'date' => get_option('date_format'),
			'time' => get_option('time_format')
		);

		//add default options
		add_option('events_maker_general', $this->defaults['general'], '', 'no');
		add_option('events_maker_templates', $this->defaults['templates'], '', 'no');
		add_option('events_maker_capabilities', '', '', 'no');
		add_option('events_maker_permalinks', $this->defaults['permalinks'], '', 'no');
		add_option('events_maker_version', $this->defaults['version'], '', 'no');

		//permalinks
		flush_rewrite_rules();
	}


	/**
	 * Execution of plugin deactivation function
	*/
	public function deactivation()
	{
		global $wp_roles;

		//remove capabilities
		foreach($wp_roles->roles as $role_name => $display_name)
		{
			$role = $wp_roles->get_role($role_name);

			foreach($this->defaults['capabilities'] as $capability)
			{
				$role->remove_cap($capability);
			}
		}

		//delete default options
		if($this->options['general']['deactivation_delete'] === TRUE)
		{
			$settings = new Events_Maker_Settings();
			$settings->update_menu();

			delete_option('events_maker_general');
			delete_option('events_maker_templates');
			delete_option('events_maker_capabilities');
			delete_option('events_maker_permalinks');
			delete_option('events_maker_version');
		}

		//permalinks
		flush_rewrite_rules();
	}


	/**
	 * 
	*/
	private function get_supports()
	{
		$supports = array();

		foreach($this->options['general']['supports'] as $support => $bool)
		{
			if($bool === TRUE)
				$supports[] = $support;
		}

		return $supports;
	}


	/**
	 * 
	*/
	public function get_defaults()
	{
		return $this->defaults;
	}


	/**
	 * 
	*/
	public function get_currencies()
	{
		return $this->currencies;
	}


	/**
	 * 
	*/
	public function get_session_id()
	{
		return $this->transient_id;
	}


	/**
	 * Generates random string
	*/
	private function generate_hash()
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
		$max = strlen($chars) - 1;
		$password = '';

		for($i = 0; $i < 64; $i++)
		{
			$password .= substr($chars, mt_rand(0, $max), 1);
		}

		return $password;
	}


	/**
	 * Initializes cookie-session
	*/
	public function init_session()
	{
		setcookie('em_transient_id', $this->transient_id, 0, COOKIEPATH, COOKIE_DOMAIN);
	}


	/**
	 * Loads text domain
	*/
	public function load_textdomain()
	{
		load_plugin_textdomain('events-maker', FALSE, EVENTS_MAKER_REL_PATH.'languages/');
	}


	/**
	 * 
	*/
	public function event_admin_notices()
	{
		global $pagenow;

		$screen = get_current_screen();
		$message_arr = get_transient($this->transient_id);

		if($screen->post_type === 'event' && $message_arr !== FALSE)
		{
			if(($pagenow === 'post.php' && $screen->id === 'event') || $screen->id === 'event_page_events-settings')
			{
				$messages = maybe_unserialize($message_arr);

				echo '
				<div id="message" class="'.$messages['status'].'">
					<p>'.$messages['text'].'</p>
				</div>';
			}

			delete_transient($this->transient_id);
		}
	}


	/**
	 * 
	*/
	public function register_map_shortcode()
	{
		add_shortcode('em-google-map', array(&$this, 'google_map_shortcode'));
	}


	/**
	 * 
	*/
	public function google_map_shortcode($args)
	{
		$markers = array();
		$map_types = array('hybrid', 'roadmap', 'satellite', 'terrain');
		$booleans = array('on', 'off');
		$defaults = array(
			'width' => '100%',
			'height' => '200px',
			'zoom' => 15,
			'maptype' => 'ROADMAP',
			'maptypecontrol' => 'on',
			'zoomcontrol' => 'on',
			'streetviewcontrol' => 'on',
			'overviewmapcontrol' => 'off',
			'pancontrol' => 'off',
			'rotatecontrol' => 'off',
			'scalecontrol' => 'off',
			'draggable' => 'on',
			'keyboardshortcuts' => 'on',
			'scrollzoom' => 'on'
		);

		$args = shortcode_atts($defaults, $args);
		$args['zoom'] = (int)$args['zoom'];

		if(!in_array(strtolower($args['maptype']), $map_types, TRUE))
			$args['maptype'] = $defaults['maptype'];

		$args['maptype'] = strtoupper($args['maptype']);
		$args['maptypecontrol'] = $this->get_proper_arg($args['maptypecontrol'], $defaults['maptypecontrol'], $booleans);
		$args['zoomcontrol'] = $this->get_proper_arg($args['zoomcontrol'], $defaults['zoomcontrol'], $booleans);
		$args['streetviewcontrol'] = $this->get_proper_arg($args['streetviewcontrol'], $defaults['streetviewcontrol'], $booleans);
		$args['overviewmapcontrol'] = $this->get_proper_arg($args['overviewmapcontrol'], $defaults['overviewmapcontrol'], $booleans);
		$args['pancontrol'] = $this->get_proper_arg($args['pancontrol'], $defaults['pancontrol'], $booleans);
		$args['rotatecontrol'] = $this->get_proper_arg($args['rotatecontrol'], $defaults['rotatecontrol'], $booleans);
		$args['scalecontrol'] = $this->get_proper_arg($args['scalecontrol'], $defaults['scalecontrol'], $booleans);
		$args['draggable'] = $this->get_proper_arg($args['draggable'], $defaults['draggable'], $booleans);
		$args['keyboardshortcuts'] = $this->get_proper_arg($args['keyboardshortcuts'], $defaults['keyboardshortcuts'], $booleans);
		$args['scrollzoom'] = $this->get_proper_arg($args['scrollzoom'], $defaults['scrollzoom'], $booleans);

		if(is_tax('event-location') || (get_post_type() === 'event' && is_single()))
		{
			$term = get_queried_object();

			if(isset($term->term_id))
			{
				$location = em_get_location($term->term_id);
				$location->location_meta['name'] = $location->name;
				$markers[] = $location->location_meta;
			}
			elseif(isset($term->ID))
			{
				$locations = em_get_locations_for($term->ID);

				if(is_array($locations) && !empty($locations))
				{
					foreach($locations as $location)
					{
						$location->location_meta['name'] = $location->name;
						$markers[] = $location->location_meta;
					}
				}
			}
		}

		wp_enqueue_script(
			'events-maker-google-maps',
			'https://maps.googleapis.com/maps/api/js?sensor=false&language='.substr(get_locale(), 0, 2)
		);

		wp_enqueue_script(
			'events-maker-front-locations',
			EVENTS_MAKER_URL.'/js/front-locations.js',
			array('jquery', 'events-maker-google-maps')
		);

		wp_localize_script(
			'events-maker-front-locations',
			'emMapArgs',
			array(
				'markers' => $markers,
				'zoom' => $args['zoom'],
				'mapTypeId' => $args['maptype'],
				'mapTypeControl' => $args['maptypecontrol'],
				'zoomControl' => $args['zoomcontrol'],
				'streetViewControl' => $args['streetviewcontrol'],
				'overviewMapControl' => $args['overviewmapcontrol'],
				'panControl' => $args['pancontrol'],
				'rotateControl' => $args['rotatecontrol'],
				'scaleControl' => $args['scalecontrol'],
				'draggable' => $args['draggable'],
				'keyboardShortcuts' => $args['keyboardshortcuts'],
				'scrollwheel' => $args['scrollzoom']
			)
		);

		echo '<div id="event-google-map" style="width: '.$args['width'].'; height: '.$args['height'].';"></div>';
	}


	/**
	 * 
	*/
	private function get_proper_arg($arg, $default, $array)
	{
		$arg = strtolower($arg);

		if(!in_array($arg, $array, TRUE))
			$arg = $default;

		if($arg === 'on')
			return 1;
		else
			return 0;
	}


	/**
	 * Registration of new custom taxonomies: event-category, event-tag, event-location, event-organizer
	*/
	public function register_taxonomies()
	{
		$labels_event_categories = array(
			'name' => _x('Event Categories', 'taxonomy general name', 'events-maker'),
			'singular_name' => _x('Event Category', 'taxonomy singular name', 'events-maker'),
			'search_items' =>  __('Search Event Categories', 'events-maker'),
			'all_items' => __('All Event Categories', 'events-maker'),
			'parent_item' => __('Parent Event Category', 'events-maker'),
			'parent_item_colon' => __('Parent Event Category:', 'events-maker'),
			'edit_item' => __('Edit Event Category', 'events-maker'),
			'view_item' => __('View Event Category', 'events-maker'),
			'update_item' => __('Update Event Category', 'events-maker'),
			'add_new_item' => __('Add New Event Category', 'events-maker'),
			'new_item_name' => __('New Event Category Name', 'events-maker'),
			'menu_name' => __('Event Categories', 'events-maker'),
		);

		$labels_event_locations = array(
			'name' => _x('Locations', 'taxonomy general name', 'events-maker'),
			'singular_name' => _x('Event Location', 'taxonomy singular name', 'events-maker'),
			'search_items' => __('Search Event Locations', 'events-maker'),
			'all_items' => __('All Event Locations', 'events-maker'),
			'parent_item' => __('Parent Event Location', 'events-maker'),
			'parent_item_colon' => __('Parent Event Location:', 'events-maker'),
			'edit_item' => __('Edit Event Location', 'events-maker'), 
			'view_item' => __('View Event Location', 'events-maker'),
			'update_item' => __('Update Event Location', 'events-maker'),
			'add_new_item' => __('Add New Event Location', 'events-maker'),
			'new_item_name' => __('New Event Location Name', 'events-maker'),
			'menu_name' => __('Locations', 'events-maker'),
		);

		$args_event_categories = array(
			'public' => TRUE,
			'hierarchical' => TRUE,
			'labels' => $labels_event_categories,
			'show_ui' => TRUE,
			'show_admin_column' => TRUE,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => TRUE,
			'rewrite' => array(
				'slug' => $this->options['permalinks']['event_rewrite_base'].'/'.$this->options['permalinks']['event_categories_rewrite_slug'],
				'with_front' => FALSE,
				'hierarchical' => TRUE
			),
			'capabilities' => array(
				'manage_terms' => 'manage_event_categories',
				'edit_terms' => 'manage_event_categories',
				'delete_terms' => 'manage_event_categories',
				'assign_terms' => 'edit_events'
			)
		);

		$args_event_locations = array(
			'public' => TRUE,
			'hierarchical' => TRUE,
			'labels' => $labels_event_locations,
			'show_ui' => TRUE,
			'show_admin_column' => TRUE,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => TRUE,
			'rewrite' => array(
				'slug' => $this->options['permalinks']['event_rewrite_base'].'/'.$this->options['permalinks']['event_locations_rewrite_slug'],
				'with_front' => FALSE,
				'hierarchical' => FALSE
			),
			'capabilities' => array(
				'manage_terms' => 'manage_event_locations',
				'edit_terms' => 'manage_event_locations',
				'delete_terms' => 'manage_event_locations',
				'assign_terms' => 'edit_events'
			)
		);

		register_taxonomy('event-category', 'event', apply_filters('em_register_event_categories', $args_event_categories));

		if($this->options['general']['use_tags'] === TRUE)
		{
			$labels_event_tags = array(
				'name' => _x('Event Tags', 'taxonomy general name', 'events-maker'),
				'singular_name' => _x('Event Tag', 'taxonomy singular name', 'events-maker'),
				'search_items' =>  __('Search Event Tags', 'events-maker'),
				'popular_items' => __('Popular Event Tags', 'events-maker'),
				'all_items' => __('All Event Tags', 'events-maker'),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __('Edit Event Tag', 'events-maker'), 
				'update_item' => __('Update Event Tag', 'events-maker'),
				'add_new_item' => __('Add New Event Tag', 'events-maker'),
				'new_item_name' => __('New Event Tag Name', 'events-maker'),
				'separate_items_with_commas' => __('Separate event tags with commas', 'events-maker'),
				'add_or_remove_items' => __('Add or remove event tags', 'events-maker'),
				'choose_from_most_used' => __('Choose from the most used event tags', 'events-maker'),
				'menu_name' => __('Event Tags', 'events-maker'),
			);

			$args_event_tags = array(
				'public' => TRUE,
				'hierarchical' => FALSE,
				'labels' => $labels_event_tags,
				'show_ui' => TRUE,
				'show_admin_column' => TRUE,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => TRUE,
				'rewrite' => array(
					'slug' => $this->options['permalinks']['event_rewrite_base'].'/'.$this->options['permalinks']['event_tags_rewrite_slug'],
					'with_front' => FALSE,
					'hierarchical' => FALSE
				),
				'capabilities' => array(
					'manage_terms' => 'manage_event_tags',
					'edit_terms' => 'manage_event_tags',
					'delete_terms' => 'manage_event_tags',
					'assign_terms' => 'edit_events'
				)
			);

			register_taxonomy('event-tag', 'event', apply_filters('em_register_event_tags', $args_event_tags));
		}

		register_taxonomy('event-location', 'event', apply_filters('em_register_event_locations', $args_event_locations));

		if($this->options['general']['use_organizers'] === TRUE)
		{
			$labels_event_organizers = array(
				'name' => _x('Organizers', 'taxonomy general name', 'events-maker'),
				'singular_name' => _x('Event Organizer', 'taxonomy singular name', 'events-maker'),
				'search_items' => __('Search Event Organizers', 'events-maker'),
				'all_items' => __('All Event Organizers', 'events-maker'),
				'parent_item' => __('Parent Event Organizer', 'events-maker'),
				'parent_item_colon' => __('Parent Event Organizer:', 'events-maker'),
				'edit_item' => __('Edit Event Organizer', 'events-maker'),
				'view_item' => __('View Event Organizer', 'events-maker'),
				'update_item' => __('Update Event Organizer', 'events-maker'),
				'add_new_item' => __('Add New Event Organizer', 'events-maker'),
				'new_item_name' => __('New Event Organizer Name', 'events-maker'),
				'menu_name' => __('Organizers', 'events-maker'),
			);

			$args_event_organizers = array(
				'public' => TRUE,
				'hierarchical' => TRUE,
				'labels' => $labels_event_organizers,
				'show_ui' => TRUE,
				'show_admin_column' => TRUE,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => TRUE,
				'rewrite' => array(
					'slug' => $this->options['permalinks']['event_rewrite_base'].'/'.$this->options['permalinks']['event_organizers_rewrite_slug'],
					'with_front' => FALSE,
					'hierarchical' => FALSE
				),
				'capabilities' => array(
					'manage_terms' => 'manage_event_organizers',
					'edit_terms' => 'manage_event_organizers',
					'delete_terms' => 'manage_event_organizers',
					'assign_terms' => 'edit_events'
				)
			);

			register_taxonomy('event-organizer', 'event', apply_filters('em_register_event_organizers', $args_event_organizers));
		}
	}


	/**
	 * Registration of new register post types: event
	*/
	public function register_post_types()
	{
		$labels_event = array(
			'name' => _x('Events', 'post type general name', 'events-maker'),
			'singular_name' => _x('Event', 'post type singular name', 'events-maker'),
			'menu_name' => __('Events', 'events-maker'),
			'all_items' => __('All Events', 'events-maker'),
			'add_new' => __('Add New', 'events-maker'),
			'add_new_item' => __('Add New Event', 'events-maker'),
			'edit_item' => __('Edit Event', 'events-maker'),
			'new_item' => __('New Event', 'events-maker'),
			'view_item' => __('View Event', 'events-maker'),
			'items_archive' => __('Event Archive', 'events-maker'),
			'search_items' => __('Search Event', 'events-maker'),
			'not_found' => __('No events found', 'events-maker'),
			'not_found_in_trash' => __('No events found in trash', 'events-maker'),
			'parent_item_colon' => ''
		);

		$taxonomies = array('event-category', 'event-location');

		if($this->options['general']['use_tags'] === TRUE)
			$taxonomies[] = 'event-tag';

		if($this->options['general']['use_organizers'] === TRUE)
			$taxonomies[] = 'event-organizer';

		$args_event = array(
			'labels' => $labels_event,
			'description' => '',
			'public' => TRUE,
			'exclude_from_search' => FALSE,
			'publicly_queryable' => TRUE,
			'show_ui' => TRUE,
			'show_in_menu' => TRUE,
			'show_in_admin_bar' => TRUE,
			'show_in_nav_menus' => TRUE,
			'menu_position' => 5,
			'menu_icon' => EVENTS_MAKER_URL.'/images/icon-events-16.png',
			'capability_type' => 'event',
			'capabilities' => array(
				'publish_posts' => 'publish_events',
				'edit_posts' => 'edit_events',
				'edit_others_posts' => 'edit_others_events',
				'edit_published_posts' => 'edit_published_events',
				'delete_published_posts' => 'delete_published_events',
				'delete_posts' => 'delete_events',
				'delete_others_posts' => 'delete_others_events',
				'read_private_posts' => 'read_private_events',
				'edit_post' => 'edit_event',
				'delete_post' => 'delete_event',
				'read_post' => 'read_event',
			),
			'map_meta_cap' => FALSE,
			'hierarchical' => FALSE,
			'supports' => $this->get_supports($this->options['general']['supports']),
			'rewrite' => array(
				'slug' => $this->options['permalinks']['event_rewrite_base'].'/'.$this->options['permalinks']['event_rewrite_slug'],
				'with_front' => FALSE,
				'feed'=> TRUE,
				'pages'=> TRUE
			),
			'has_archive' => $this->options['permalinks']['event_rewrite_base'],
			'query_var' => TRUE,
			'can_export' => TRUE,
			'taxonomies' => $taxonomies,
		);

		register_post_type('event', apply_filters('em_register_post_type', $args_event));
	}


	/**
	 * Custom post type messages
	*/
	public function register_post_types_messages($messages)
	{
		global $post, $post_ID;

		$messages['event'] = array(
			0 => '', //Unused. Messages start at index 1.
			1 => sprintf(__('Event updated. <a href="%s">View event</a>', 'events-maker'), esc_url(get_permalink($post_ID))),
			2 => __('Custom field updated.', 'events-maker'),
			3 => __('Custom field deleted.', 'events-maker'),
			4 => __('Event updated.', 'events-maker'),
			//translators: %s: date and time of the revision
			5 => isset($_GET['revision']) ? sprintf(__('Event restored to revision from %s', 'events-maker'), wp_post_revision_title((int)$_GET['revision'], FALSE)) : FALSE,
			6 => sprintf(__('Event published. <a href="%s">View event</a>', 'events-maker'), esc_url(get_permalink($post_ID))),
			7 => __('Event saved.', 'events-maker'),
			8 => sprintf(__('Event submitted. <a target="_blank" href="%s">Preview event</a>', 'events-maker'), esc_url( add_query_arg('preview', 'true', get_permalink($post_ID)))),
			9 => sprintf(__('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'events-maker'),
			//translators: Publish box date format, see http://php.net/date
			date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
			10 => sprintf(__('Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'events-maker'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))))
		);
	
		return $messages;
	}


	/**
	 * 
	*/
	public function admin_scripts_styles($page)
	{
		$screen = get_current_screen();

		//event location taxonomy
		if($page === 'edit-tags.php' && $screen->id === 'edit-event-location' && $screen->taxonomy === 'event-location' && $screen->post_type === 'event')
		{
			$timezone = explode('/', get_option('timezone_string'));

			wp_register_script(
				'events-maker-google-maps',
				'https://maps.googleapis.com/maps/api/js?sensor=false&language='.substr(get_locale(), 0, 2)
			);
			wp_enqueue_script('events-maker-google-maps');

			wp_register_script(
				'events-maker-admin-locations',
				EVENTS_MAKER_URL.'/js/admin-locations.js',
				array('jquery', 'events-maker-google-maps')
			);
			wp_enqueue_script('events-maker-admin-locations');

			wp_localize_script(
				'events-maker-admin-locations',
				'emArgs',
				array('country' => $timezone[1])
			);

			wp_register_style(
				'events-maker-admin',
				EVENTS_MAKER_URL.'/css/admin.css'
			);
			wp_enqueue_style('events-maker-admin');
		}
		//widgets
		elseif($page === 'widgets.php')
		{
			wp_register_script(
				'events-maker-admin-widgets',
				EVENTS_MAKER_URL.'/js/admin-widgets.js',
				array('jquery')
			);
			wp_enqueue_script('events-maker-admin-widgets');

			wp_register_style(
				'events-maker-admin',
				EVENTS_MAKER_URL.'/css/admin.css'
			);
			wp_enqueue_style('events-maker-admin');
		}
		//event options page
		elseif($page === 'event_page_events-settings')
		{
			wp_register_script(
				'events-maker-admin-settings',
				EVENTS_MAKER_URL.'/js/admin-settings.js',
				array('jquery', 'jquery-ui-core', 'jquery-ui-button')
			);
			wp_enqueue_script('events-maker-admin-settings');

			wp_localize_script(
				'events-maker-admin-settings',
				'emArgs',
				array(
					'resetToDefaults' => __('Are you sure you want to reset these settings to defaults?', 'events-maker')
				)
			);

			wp_register_style(
				'events-maker-admin',
				EVENTS_MAKER_URL.'/css/admin.css'
			);
			wp_enqueue_style('events-maker-admin');

			wp_register_style(
				'events-maker-wplike',
				EVENTS_MAKER_URL.'/css/wp-like-ui-theme.css'
			);
			wp_enqueue_style('events-maker-wplike');
		}
		//list of events
		elseif($page === 'edit.php' && $screen->post_type === 'event')
		{
			global $wp_locale;

			wp_register_script(
				'events-maker-admin-edit',
				EVENTS_MAKER_URL.'/js/admin-edit.js',
				array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker')
			);
			wp_enqueue_script('events-maker-admin-edit');

			wp_localize_script(
				'events-maker-admin-edit',
				'emEditArgs',
				array(
					'firstWeekDay' => $this->options['general']['first_weekday'],
					'monthNames' => array_values($wp_locale->month),
					'monthNamesShort' => array_values($wp_locale->month_abbrev),
					'dayNames' => array_values($wp_locale->weekday),
					'dayNamesShort' => array_values($wp_locale->weekday_abbrev),
					'dayNamesMin' => array_values($wp_locale->weekday_initial),
					'isRTL' => $wp_locale->is_rtl()
				)
			);

			wp_register_style(
				'events-maker-admin',
				EVENTS_MAKER_URL.'/css/admin.css'
			);
			wp_enqueue_style('events-maker-admin');

			wp_register_style(
				'events-maker-wplike',
				EVENTS_MAKER_URL.'/css/wp-like-ui-theme.css'
			);
			wp_enqueue_style('events-maker-wplike');
		}
	}


	/**
	 * 
	*/
	public function front_scripts_styles()
	{
		wp_register_style(
			'events-maker-front',
			EVENTS_MAKER_URL.'/css/front.css'
		);
		wp_enqueue_style('events-maker-front');
	}


	/**
	 * Edit screen icon
	*/
	public function edit_screen_icon()
	{
		global $post;

		if(get_post_type($post) === 'event' || (isset($_GET['post_type']) && $_GET['post_type'] === 'event'))
		{
			echo '
			<style>
				#icon-edit { background: transparent url(\''.EVENTS_MAKER_URL.'/images/icon-events-32.png\') no-repeat; }
			</style>';
		}
	}


	/**
	 * Adds links to Support Forum
	*/
	public function plugin_extend_links($links, $file) 
	{
		if(!current_user_can('install_plugins'))
			return $links;

		$plugin = plugin_basename(__FILE__);

		if($file == $plugin)
		{
			return array_merge(
				$links,
				array(sprintf('<a href="http://www.dfactory.eu/support/forum/events-maker/" target="_blank">%s</a>', __('Support', 'events-maker')))
			);
		}

		return $links;
	}


	/**
	 * Maps capabilities
	*/
	public function event_map_meta_cap($caps, $cap, $user_id, $args)
	{
		if('edit_event' === $cap || 'delete_event' === $cap || 'read_event' === $cap)
		{
			$post = get_post($args[0]);
			$post_type = get_post_type_object($post->post_type);
			$caps = array();

			if($post->post_type !== 'event')
				return $caps;
		}

		if('edit_event' === $cap)
		{
			if ($user_id == $post->post_author)
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}
		elseif('delete_event' === $cap)
		{
			if (isset($post->post_author) && $user_id == $post->post_author)
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}
		elseif('read_event' === $cap)
		{
			if ('private' != $post->post_status)
				$caps[] = 'read';
			elseif ($user_id == $post->post_author)
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		return $caps;
	}
}
?>