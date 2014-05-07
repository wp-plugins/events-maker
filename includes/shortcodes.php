<?php
if(!defined('ABSPATH')) exit;

new Events_Maker_Shortcodes($events_maker);

class Events_Maker_Shortcodes
{
	private $options = array();
	private $events_maker;


	public function __construct($events_maker)
	{
		// settings
		$this->options = $events_maker->get_options();

		// main object
		$this->events_maker = $events_maker;

		// actions
		add_action('init', array(&$this, 'register_shortcodes'));
		add_action('init', array(&$this, 'check_calendar_page'));
		add_action('deleted_post', array(&$this, 'after_delete_trash_calendar_page'));
		add_action('transition_post_status', array(&$this, 'after_change_status_calendar_page'), 10, 3);

		// filters
		add_filter('the_content', array(&$this, 'add_full_calendar'));
	}


	/**
	 *
	*/
	public function after_delete_trash_calendar_page($post_id)
	{
		// wpml or polylang?
		if(function_exists('icl_object_id'))
			$check_id = (int)icl_object_id($post_id, 'page', true);
		else
			$check_id = (int)$post_id;

		if(get_post_type($post_id) === 'page' && $post_id === $check_id)
		{
			$this->options['general']['full_calendar_display']['type'] = 'manual';
			$this->options['general']['full_calendar_display']['page'] = 0;

			update_option('events_maker_general', $this->options['general']);
		}
	}


	/**
	 * 
	*/
	public function after_change_status_calendar_page($new_status, $old_status, $post)
	{
		if($post->post_type === 'page' && $old_status === 'publish' && $new_status !== 'publish')
		{
			// wpml or polylang?
			if(function_exists('icl_object_id'))
				$check_id = (int)icl_object_id($post->ID, 'page', true);
			else
				$check_id = (int)$post->ID;

			if($post->ID === $check_id)
			{
				$this->options['general']['full_calendar_display']['type'] = 'manual';
				$this->options['general']['full_calendar_display']['page'] = 0;

				update_option('events_maker_general', $this->options['general']);
			}
		}
	}


	/**
	 * 
	*/
	public function add_full_calendar($content)
	{
		if($this->options['general']['full_calendar_display']['type'] === 'page' && is_page($this->options['general']['full_calendar_display']['page']))
		{
			if($this->options['general']['full_calendar_display']['content'] === 'before')
				$content = '[em-full-calendar]'.$content;
			else
				$content = $content.'[em-full-calendar]';
		}

		return $content;
	}


	/**
	 * 
	*/
	public function check_calendar_page()
	{
		if(!current_user_can('manage_options'))
			return;

		// creating page?
		if(isset($_POST['events_maker_create_page']))
		{
			// for all network sites
			if(is_multisite() && is_network_admin())
			{
				global $wpdb;

				$current_blog_id = $wpdb->blogid;
				$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));
				$success = $fail = 0;

				foreach($blogs_ids as $blog_id)
				{
					switch_to_blog($blog_id);

					if($this->options['general']['display_page_notice'])
					{
						if($this->create_calendar_page(true))
							$success++;
						else
							$fail++;
					}
				}

				if($success > 0 && $fail === 0)
					$this->events_maker->display_notice(__('Calendar page was created successfully on all of the network sites.', 'events-maker'), 'updated', true);
				elseif($success === 0 && $fail > 0)
					$this->events_maker->display_notice(__('Calendar page was not created on all of the network sites. You can try to create it manually later.', 'events-maker'), 'error', true);
				else
					$this->events_maker->display_notice(__('Calendar page was not created on some of the network sites. You can try to create it manually later.', 'events-maker'), 'error', true);

				switch_to_blog($current_blog_id);
			}
			else
				$this->create_calendar_page();
		}
		elseif(isset($_POST['events_maker_decline']))
		{
			// for all network sites
			if(is_multisite() && is_network_admin())
			{
				global $wpdb;

				$current_blog_id = $wpdb->blogid;
				$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));

				foreach($blogs_ids as $blog_id)
				{
					switch_to_blog($blog_id);

					$this->options['general']['full_calendar_display']['type'] = 'manual';
					$this->options['general']['full_calendar_display']['page'] = 0;

					// do not display notice anymore
					$this->options['general']['display_page_notice'] = false;

					// updates general settings
					update_option('events_maker_general', $this->options['general']);
				}

				switch_to_blog($current_blog_id);
			}
			else
			{
				$this->options['general']['full_calendar_display']['type'] = 'manual';
				$this->options['general']['full_calendar_display']['page'] = 0;

				// do not display notice anymore
				$this->options['general']['display_page_notice'] = false;

				// updates general settings
				update_option('events_maker_general', $this->options['general']);
			}
		}

		$calendar_page_html = '
		<form action="" method="post">
			<p>'.__('Events Maker needs to create a page for your events calendar display. Would you like to do it automatically?', 'events-maker').' <input type="submit" class="button button-primary button-small" name="events_maker_create_page" value="'.__('Create Calendar Page', 'events-maker').'"/> <input type="submit" class="button button-small" name="events_maker_decline" value="'.__('No thanks', 'events-maker').'"/></p>
		</form>';

		if(is_multisite() && is_network_admin())
		{
			global $wpdb;

			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));
			$notice_required = false;

			foreach($blogs_ids as $blog_id)
			{
				switch_to_blog($blog_id);

				if($this->options['general']['display_page_notice'])
					$notice_required = true;
			}

			if($notice_required)
				$this->events_maker->display_notice($calendar_page_html, 'updated');

			switch_to_blog($current_blog_id);
		}
		else
		{
			if($this->options['general']['display_page_notice'])
				$this->events_maker->display_notice($calendar_page_html, 'updated');
		}
	}


	/**
	 * 
	*/
	public function create_calendar_page($network = false)
	{
		$id = wp_insert_post(
			array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_title' => __('Events Calendar', 'events-maker')
			),
			false
		);

		// if everything went fine
		if(is_int($id) && $id > 0)
		{
			$this->options['general']['full_calendar_display']['type'] = 'page';
			$this->options['general']['full_calendar_display']['page'] = $id;

			// wpml and polylang compatibility
			if(function_exists('icl_object_id'))
				$this->options['general']['full_calendar_display']['page'] = icl_object_id($id, 'page', true);

			if($network)
				$page = true;
			else
				$this->events_maker->display_notice(__('Calendar page was created successfully.', 'events-maker'), 'updated', true);
		}
		else
		{
			$this->options['general']['full_calendar_display']['type'] = 'manual';
			$this->options['general']['full_calendar_display']['page'] = 0;

			if($network)
				$page = false;
			else
				$this->events_maker->display_notice(__('Calendar page was not created successfully. You can try to create it manually later.', 'events-maker'), 'error', true);
		}

		// do not display notice anymore
		$this->options['general']['display_page_notice'] = false;

		// updates general settings
		update_option('events_maker_general', $this->options['general']);

		if($network)
			return $page;
	}


	/**
	 * 
	*/
	public function register_shortcodes()
	{
		add_shortcode('em-full-calendar', array(&$this, 'calendar_shortcode'));
		add_shortcode('em-google-map', array(&$this, 'google_map_shortcode'));
	}


	/**
	 * 
	*/
	public function calendar_shortcode($args)
	{
		$defaults = array(
			'start_after' => '',
			'start_before' => '',
			'end_after' => '',
			'end_before' => '',
			'ondate' => '',
			'date_range' => 'between',
			'date_type' => 'all',
			'ticket_type' => 'all',
			'show_past_events' => $this->options['general']['show_past_events'],
			'show_occurrences' => $this->options['general']['show_occurrences'],
			'post_type' => 'event',
			'author' => ''
		);

		// parse arguments
		$args = shortcode_atts($defaults, $args);

		// makes strings
		$args['start_after'] = (string)$args['start_after'];
		$args['start_before'] = (string)$args['start_before'];
		$args['end_after'] = (string)$args['end_after'];
		$args['end_before'] = (string)$args['end_before'];
		$args['ondate'] = (string)$args['ondate'];

		// valid date range?
		if(!in_array($args['date_range'], array('between', 'outside'), true))
			$args['date_range'] = $defaults['date_range'];

		// valid date type?
		if(!in_array($args['date_type'], array('all', 'all_day', 'not_all_day'), true))
			$args['date_type'] = $defaults['date_type'];

		// valid ticket type?
		if(!in_array($args['ticket_type'], array('all', 'free', 'paid'), true))
			$args['ticket_type'] = $defaults['ticket_type'];

		// makes bitwise integers
		$args['show_past_events'] = (bool)(int)$args['show_past_events'];
		$args['show_occurrences'] = (bool)(int)$args['show_occurrences'];

		$authors = $users = array();

		if(trim($args['author']) !== '')
			$users = explode(',', $args['author']);

		if(!empty($users))
		{
			foreach($users as $author)
			{
				$authors[] = (int)$author;
			}

			// removes possible duplicates
			$args['author__in'] = array_unique($authors);
		}

		// unset author argument
		unset($args['author']);

		// sets new arguments
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

		// unsets old arguments
		unset($args['start_after']);
		unset($args['start_before']);
		unset($args['end_after']);
		unset($args['end_before']);
		unset($args['ondate']);
		unset($args['date_range']);
		unset($args['date_type']);
		unset($args['ticket_type']);
		unset($args['show_past_events']);
		unset($args['show_occurrences']);

		wp_register_script(
			'events-maker-moment',
			EVENTS_MAKER_URL.'/assets/fullcalendar/moment.min.js',
			array('jquery')
		);

		wp_register_script(
			'events-maker-fullcalendar',
			EVENTS_MAKER_URL.'/assets/fullcalendar/fullcalendar.min.js',
			array('jquery', 'events-maker-moment')
		);

		wp_register_script(
			'events-maker-front-calendar',
			EVENTS_MAKER_URL.'/js/front-calendar.js',
			array('jquery', 'jquery-ui-core', 'events-maker-fullcalendar')
		);

		wp_enqueue_script('events-maker-front-calendar');

		$locale = str_replace('_', '-', strtolower(get_locale()));
		$locale_code = explode('-', $locale);

		if(file_exists(EVENTS_MAKER_PATH.'assets/fullcalendar/lang/'.$locale.'.js'))
			$lang_path = EVENTS_MAKER_URL.'/assets/fullcalendar/lang/'.$locale.'.js';
		elseif(file_exists(EVENTS_MAKER_PATH.'assets/fullcalendar/lang/'.$locale_code[0].'.js'))
			$lang_path = EVENTS_MAKER_URL.'/assets/fullcalendar/lang/'.$locale_code[0].'.js';

		if(isset($lang_path))
		{
			wp_register_script(
				'events-maker-front-calendar-lang',
				$lang_path,
				array('jquery', 'jquery-ui-core', 'events-maker-front-calendar')
			);

			wp_enqueue_script('events-maker-front-calendar-lang');
		}

		wp_localize_script(
			'events-maker-front-calendar',
			'emCalendarArgs',
			array(
				'firstWeekDay' => ($this->options['general']['first_weekday'] === 7 ? 0 : 1),
				'events' => $this->get_calendar_events($args)
			)
		);

		wp_register_style(
			'events-maker-front-calendar',
			EVENTS_MAKER_URL.'/assets/fullcalendar/fullcalendar.css'
		);

		wp_register_style(
			'events-maker-front-calendar-print',
			EVENTS_MAKER_URL.'/assets/fullcalendar/fullcalendar.print.css',
			array(),
			false,
			'print'
		);

		wp_enqueue_style('events-maker-front-calendar');
		wp_enqueue_style('events-maker-front-calendar-print');

		return '<div id="events-full-calendar"></div>';
	}


	/**
	 * 
	*/
	private function get_calendar_events($args)
	{
		$events = em_get_events($args);
		$calendar = array();

		if(empty($events))
			return $calendar;

		foreach($events as $event)
		{
			$id = $event->ID;

			if(em_is_recurring($id))
			{
				$start = $event->event_occurrence_start_date;
				$end = $event->event_occurrence_end_date;
			}
			else
			{
				$start = get_post_meta($id, '_event_start_date', true);
				$end = get_post_meta($id, '_event_end_date', true);
			}

			$calendar[] = array(
				'title' => $event->post_title,
				'start' => $start,
				'end' => $end,
				'allDay' => em_is_all_day($id),
				'url' => get_permalink($id)
			);
		}

		return $calendar;
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
			'locations' => '',
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

		//location ids
		$locations = ($args['locations'] !== '' ? explode(',', $args['locations']) : '');

		if(is_array($locations) && !empty($locations))
		{
			$locations_tmp = array();

			foreach($locations as $location)
			{
				$locations_tmp[] = (int)$location;
			}

			foreach(array_unique($locations_tmp) as $location_id)
			{
				$location = em_get_location($location_id);
				$location->location_meta['name'] = $location->name;
				$markers[] = $location->location_meta;
			}
		}
		elseif(is_tax('event-location') || (in_array(get_post_type(), apply_filters('em_event_post_type', array('event'))) && is_single()))
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

		wp_register_script(
			'events-maker-google-maps',
			'https://maps.googleapis.com/maps/api/js?sensor=false&language='.substr(get_locale(), 0, 2)
		);

		wp_register_script(
			'events-maker-front-locations',
			EVENTS_MAKER_URL.'/js/front-locations.js',
			array('jquery', 'events-maker-google-maps')
		);

		wp_enqueue_script('events-maker-front-locations');

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

		return '<div id="event-google-map" style="width: '.$args['width'].'; height: '.$args['height'].';"></div>';
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
}
?>