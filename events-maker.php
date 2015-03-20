<?php
/*
Plugin Name: Events Maker
Description: Fully featured event management system including recurring events, locations management, full calendar, iCal feed/files, google maps and more.
Version: 1.5.1
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

if(!defined('ABSPATH')) exit;

define('EVENTS_MAKER_URL', plugins_url('', __FILE__));	
define('EVENTS_MAKER_PATH', plugin_dir_path(__FILE__));
define('EVENTS_MAKER_REL_PATH', dirname(plugin_basename(__FILE__)).'/');	
define('EVENTS_MAKER_UPDATE_VERSION_1', '1.0.10');
		
include_once(EVENTS_MAKER_PATH.'includes/core-functions.php');
include_once(EVENTS_MAKER_PATH.'includes/class-ical.php');
include_once(EVENTS_MAKER_PATH.'includes/class-helper.php');
include_once(EVENTS_MAKER_PATH.'includes/class-localisation.php');
include_once(EVENTS_MAKER_PATH.'includes/class-listing.php');
include_once(EVENTS_MAKER_PATH.'includes/class-metaboxes.php');
include_once(EVENTS_MAKER_PATH.'includes/class-query.php');
include_once(EVENTS_MAKER_PATH.'includes/class-post-types.php');
include_once(EVENTS_MAKER_PATH.'includes/class-shortcodes.php');
include_once(EVENTS_MAKER_PATH.'includes/class-taxonomies.php');
include_once(EVENTS_MAKER_PATH.'includes/class-templates.php');
include_once(EVENTS_MAKER_PATH.'includes/class-widgets.php');
include_once(EVENTS_MAKER_PATH.'includes/class-settings.php');
include_once(EVENTS_MAKER_PATH.'includes/class-update.php');
include_once(EVENTS_MAKER_PATH.'includes/class-welcome.php');

final class Events_Maker
{
	private static $_instance;
	public $options = array();
	public $recurrences = array();
	public $notices = array();
	public $defaults = array(
		'general' => array(
			'supports' => array(
				'title' => true,
				'editor' => true,
				'author' => true,
				'thumbnail' => true,
				'excerpt' => true,
				'custom-fields' => false,
				'comments' => true,
				'trackbacks' => false,
				'revisions' => false,
				'gallery' => true
			),
			'display_page_notice' => true,
			'order_by' => 'start',
			'order' => 'asc',
			'expire_current' => false,
			'show_past_events' => true,
			'show_occurrences' => true,
			'use_organizers' => true,
			'use_tags' => true,
			'use_event_tickets' => true,
			'default_event_options' => array(
				'google_map' => true,
				'display_gallery' => true,
				'display_location_details' => true,
				'price_tickets_info' => true,
				'display_organizer_details' => true
			),
			'full_calendar_display' => array(
				'type' => 'manual',
				'page' => 0,
				'content' => 'after'
			),
			'ical_feed' => true,
			'events_in_rss' => true,
			'deactivation_delete' => false,
			'event_nav_menu' => array(
				'show' => false,
				'menu_name' => '',
				'menu_id' => 0,
				'item_id' => 0
			),
			'datetime_format' => array(
				'date' => '',
				'time' => ''
			),
			'first_weekday' => 1,
			'rewrite_rules' => true,
			'currencies' => array(
				'code' => 'usd',
				'symbol' => '$',
				'position' => 'after',
				'format' => 1
			)
		),
		'templates' => array(
			'default_templates' => true
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
		'version' => '1.5.1'
	);
	private $transient_id = '';


	private function __clone() {}
	private function __wakeup() {}


	/**
	 * Main Events Maker instance
	 */
	public static function instance()
	{
		if(self::$_instance === null)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	
	/**
	 * Events Maker constructor
	 */
	public function __construct()
	{
		register_activation_hook(__FILE__, array(&$this, 'multisite_activation'));
		register_deactivation_hook(__FILE__, array(&$this, 'multisite_deactivation'));

		// settings
		$this->options = array(
			'general' => array_merge($this->defaults['general'], get_option('events_maker_general', $this->defaults['general'])),
			'permalinks' => array_merge($this->defaults['permalinks'], get_option('events_maker_permalinks', $this->defaults['permalinks'])),
			'templates' => array_merge($this->defaults['templates'], get_option('events_maker_templates', $this->defaults['templates']))
		);

		// session id
		$this->transient_id = (isset($_COOKIE['em_transient_id']) ? $_COOKIE['em_transient_id'] : 'emtr_'.sha1($this->generate_hash()));

		// actions
		add_action('plugins_loaded', array(&$this, 'init_session'), 1);
		add_action('plugins_loaded', array(&$this, 'load_textdomain'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts_styles'));
		add_action('wp_enqueue_scripts', array(&$this, 'front_scripts_styles'));
		add_action('admin_notices', array(&$this, 'event_admin_notices'));
		add_action('after_setup_theme', array(&$this, 'load_defaults'));
		add_action('wp', array(&$this, 'load_pluggable_functions'));
		add_action('wp', array(&$this, 'load_pluggable_hooks'));

		// filters
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_settings_link'));
		add_filter('plugin_row_meta', array(&$this, 'plugin_extend_links'), 10, 2);

		do_action('em_loaded');
	}


	/**
	 * Multisite activation
	 */
	public function multisite_activation($networkwide)
	{
		if(is_multisite() && $networkwide)
		{
			global $wpdb;

			$activated_blogs = array();
			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));

			foreach($blogs_ids as $blog_id)
			{
				switch_to_blog($blog_id);
				$this->activate_single();
				$activated_blogs[] = (int)$blog_id;
			}

			switch_to_blog($current_blog_id);
			update_site_option('events_maker_activated_blogs', $activated_blogs, array());
		}
		else
			$this->activate_single();
	}


	/**
	 * Single site activation
	 */
	public function activate_single()
	{
		global $wp_roles;

		// transient for welcome screen
		set_transient('_events_maker_activation_redirect', 1, 3600);

		// add caps to administrators
		foreach($wp_roles->roles as $role_name => $display_name)
		{
			$role = $wp_roles->get_role($role_name);

			if($role->has_cap('manage_options'))
			{
				foreach($this->defaults['capabilities'] as $capability)
				{
					if((!$this->defaults['general']['use_tags'] && $capability === 'manage_event_tags') || (!$this->defaults['general']['use_organizers'] && $capability === 'manage_event_organizers'))
						continue;

					$role->add_cap($capability);
				}
			}
		}

		$this->defaults['general']['datetime_format'] = array(
			'date' => get_option('date_format'),
			'time' => get_option('time_format')
		);

		// add default options
		add_option('events_maker_general', $this->defaults['general'], '', 'no');
		add_option('events_maker_templates', $this->defaults['templates'], '', 'no');
		add_option('events_maker_capabilities', '', '', 'no');
		add_option('events_maker_permalinks', $this->defaults['permalinks'], '', 'no');
		add_option('events_maker_version', $this->defaults['version'], '', 'no');

		// permalinks
		flush_rewrite_rules();
	}


	/**
	 * Multisite deactivation
	 */
	public function multisite_deactivation($networkwide)
	{
		if(is_multisite() && $networkwide)
		{
			global $wpdb;

			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));

			if(!($activated_blogs = get_site_option('events_maker_activated_blogs', false, false)))
				$activated_blogs = array();

			foreach($blogs_ids as $blog_id)
			{
				switch_to_blog($blog_id);
				$this->deactivate_single(true);

				if(in_array((int)$blog_id, $activated_blogs, true))
					unset($activated_blogs[array_search($blog_id, $activated_blogs)]);
			}

			switch_to_blog($current_blog_id);
			update_site_option('events_maker_activated_blogs', $activated_blogs);
		}
		else
			$this->deactivate_single();
	}


	/**
	 * Single site deactivation
	 */
	public function deactivate_single($multi = false)
	{
		global $wp_roles;

		// remove capabilities
		foreach($wp_roles->roles as $role_name => $display_name)
		{
			$role = $wp_roles->get_role($role_name);

			foreach($this->defaults['capabilities'] as $capability)
			{
				$role->remove_cap($capability);
			}
		}

		if($multi)
		{
			$options = get_option('events_maker_general');
			$check = $options['deactivation_delete'];
		}
		else
			$check = $this->options['general']['deactivation_delete'];

		// delete default options
		if($check)
		{
			$settings = new Events_Maker_Settings($this);
			$settings->update_menu();

			delete_option('events_maker_general');
			delete_option('events_maker_templates');
			delete_option('events_maker_capabilities');
			delete_option('events_maker_permalinks');
		}

		// permalinks
		flush_rewrite_rules();
	}
	
	/**
	 * Add link to Settings page
	 */
	public function plugin_settings_link($links) 
	{
		if(!is_admin() || !current_user_can('install_plugins'))
			return $links;

		$links[] = sprintf('<a href="%s">%s</a>', admin_url('edit.php').'?post_type=event&page=events-settings', __('Settings', 'events-maker'));

		return $links;
	}


	/**
	 * Add links to Support Forum
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
	 * Load defaults
	 */
	public function load_defaults()
	{
		$this->recurrences = apply_filters(
			'em_event_recurrences_options',
			array(
				'once' => __('once', 'events-maker'),
				'daily' => __('daily', 'events-maker'),
				'weekly' => __('weekly', 'events-maker'),
				'monthly' => __('monthly', 'events-maker'),
				'yearly' => __('yearly', 'events-maker'),
				'custom' => __('custom', 'events-maker')
			)
		);
	}


	/**
	 * Load pluggable template functions
	 */
	public function load_pluggable_functions() 
	{
		include_once(EVENTS_MAKER_PATH.'includes/template-functions.php');
	}
	
	
	/**
	 * Load pluggable template hooks
	 */
	public function load_pluggable_hooks() 
	{
		include_once(EVENTS_MAKER_PATH.'includes/template-hooks.php');
	}


	/**
	 * Get session id
	 */
	public function get_session_id()
	{
		return $this->transient_id;
	}


	/**
	 * Generate random string
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
	 * Initialize cookie-session
	 */
	public function init_session()
	{
		setcookie('em_transient_id', $this->transient_id, 0, COOKIEPATH, COOKIE_DOMAIN);
	}


	/**
	 * Load text domain
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain('events-maker', false, EVENTS_MAKER_REL_PATH.'languages/');
	}


	/**
	 * Print admin notices
	 */
	public function event_admin_notices()
	{
		global $pagenow;

		$screen = get_current_screen();
		$message_arr = get_transient($this->transient_id);

		if($screen->post_type === 'event' && $message_arr !== false)
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
	 * Print admin notices
	 */
	public function display_notice($html = '', $status = 'error', $paragraph = false, $network = true)
	{
		$this->notices[] = array(
			'html' => $html,
			'status' => $status,
			'paragraph' => $paragraph
		);

		add_action('admin_notices', array(&$this, 'admin_display_notice'));

		if($network)
			add_action('network_admin_notices', array(&$this, 'admin_display_notice'));
	}


	/**
	 * Print admin notices
	 */
	public function admin_display_notice()
	{
		foreach($this->notices as $notice)
		{
			echo '
			<div class="events-maker '.$notice['status'].'">
				'.($notice['paragraph'] ? '<p>' : '').'
				'.$notice['html'].'
				'.($notice['paragraph'] ? '</p>' : '').'
			</div>';
		}
	}


	/**
	 * Enqueue admin scripts and style
	 */
	public function admin_scripts_styles($pagenow)
	{
		$screen = get_current_screen();

		wp_register_style(
			'events-maker-admin',
			EVENTS_MAKER_URL.'/css/admin.css'
		);

		wp_register_style(
			'events-maker-wplike',
			EVENTS_MAKER_URL.'/css/wp-like-ui-theme.css'
		);

		if($pagenow === 'edit-tags.php' && in_array($screen->post_type, apply_filters('em_event_post_type', array('event'))))
		{
			// event location & organizer
			if(($screen->id === 'edit-event-organizer' && $screen->taxonomy === 'event-organizer') || ($screen->id === 'edit-event-location' && $screen->taxonomy === 'event-location') || ($screen->id === 'edit-event-category' && $screen->taxonomy === 'event-category'))
			{
				$timezone = explode('/', get_option('timezone_string'));
				
				if(!isset($timezone[1]))
					$timezone[1] = 'United Kingdom, London';
				
				wp_enqueue_media();
				wp_enqueue_style('wp-color-picker');

				wp_register_script(
					'events-maker-edit-tags',
					EVENTS_MAKER_URL.'/js/admin-tags.js',
					array('jquery', 'wp-color-picker', 'jquery-touch-punch')
				);

				wp_enqueue_script('events-maker-edit-tags');
				
				wp_register_script(
					'events-maker-google-maps',
					'https://maps.googleapis.com/maps/api/js?sensor=false&language='.substr(get_locale(), 0, 2)
				);
				
				// on event locations only
				if ($screen->id === 'edit-event-location')
					wp_enqueue_script('events-maker-google-maps');

				wp_localize_script(
					'events-maker-edit-tags',
					'emArgs',
					array(
						'title' => __('Select image', 'events-maker'),
						'button' => array('text' => __('Add image', 'events-maker')),
						'frame' => 'select',
						'multiple' => false,
						'country' => $timezone[1]
					)
				);
				
				wp_enqueue_style('events-maker-admin');
			}
		}
		// widgets
		elseif($pagenow === 'widgets.php')
		{
			wp_register_script(
				'events-maker-admin-widgets',
				EVENTS_MAKER_URL.'/js/admin-widgets.js',
				array('jquery')
			);

			wp_enqueue_script('events-maker-admin-widgets');
			wp_enqueue_style('events-maker-admin');
		}
		// event options page
		elseif($pagenow === 'event_page_events-settings')
		{
			wp_register_script(
				'events-maker-admin-settings',
				EVENTS_MAKER_URL.'/js/admin-settings.js',
				array('jquery')
			);

			wp_enqueue_script('events-maker-admin-settings');

			wp_localize_script(
				'events-maker-admin-settings',
				'emArgs',
				array(
					'resetToDefaults' => __('Are you sure you want to reset these settings to defaults?', 'events-maker')
				)
			);

			wp_enqueue_style('events-maker-admin');
		}
		// list of events
		elseif($pagenow === 'edit.php' && in_array($screen->post_type, apply_filters('em_event_post_type', array('event'))))
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
					'isRTL' => $wp_locale->is_rtl(),
					'nonce' => wp_create_nonce('events-maker-feature-event')
				)
			);

			wp_enqueue_style('events-maker-admin');
			wp_enqueue_style('events-maker-wplike');
		}
		// update
		elseif($pagenow === 'event_page_events-maker-update')
			wp_enqueue_style('events-maker-admin');
	}


	/**
	 * Enqueue frontend scripts and style
	 */
	public function front_scripts_styles()
	{
		wp_register_style(
			'events-maker-front',
			EVENTS_MAKER_URL.'/css/front.css'
		);

		wp_enqueue_style('events-maker-front');
		
		wp_register_script(
			'events-maker-sorting',
			EVENTS_MAKER_URL.'/js/front-sorting.js',
			array('jquery')
		);

		wp_enqueue_script('events-maker-sorting');
	}
}


/**
 * Initialise Events Maker
 */
function Events_Maker()
{
	static $instance;

  	// first call to instance() initializes the plugin
  	if($instance === null || !($instance instanceof Events_Maker))
    	$instance = Events_Maker::instance();

  	return $instance;
}

Events_Maker();
?>