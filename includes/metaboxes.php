<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

$events_maker_metaboxes = new Events_Maker_Metaboxes($events_maker);

class Events_Maker_Metaboxes
{
	private $errors = array();
	private $metaboxes = array();
	private $options = array();
	private $tickets_fields = array();
	private $transient_id = '';
	private $events_maker;


	public function __construct($events_maker)
	{
		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general'))
		);

		//passed vars
		$this->transient_id = $events_maker->get_session_id();

		//actions
		add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts_styles'));
		add_action('add_meta_boxes_event', array(&$this, 'add_events_meta_boxes'));
		add_action('after_setup_theme', array(&$this, 'load_defaults'));
		add_action('save_post', array(&$this, 'save_event'), 10, 2);
	}


	/**
	 * 
	*/
	public function load_defaults()
	{
		$this->tickets_fields = apply_filters(
			'em_event_tickets_fields',
			array(
				'name' => __('Ticket Name', 'events-maker'),
				'price' => __('Price', 'events-maker')
			)
		);

		$this->errors = array(
			'last_day_wrong_date_input' => __('Invalid Until date.', 'events-maker'),
			'last_day_wrong_date' => __('Such Until date does not exists.', 'events-maker'),
			'start_wrong_date_input' => __('Invalid Start date.', 'events-maker'),
			'start_wrong_date' => __('Such Start date does not exists.', 'events-maker'),
			'start_wrong_time_input' => __('Invalid Start time.', 'events-maker'),
			'start_wrong_time' => __('Such Start time does not exists.', 'events-maker'),
			'end_wrong_date_input' => __('Invalid End date.', 'events-maker'),
			'end_wrong_date' => __('Such End date does not exists.', 'events-maker'),
			'end_wrong_time_input' => __('Invalid End time.', 'events-maker'),
			'end_wrong_time' => __('Such End time does not exists.', 'events-maker'),
			'empty_tickets' => __('No tickets were added to a paid event.', 'events-maker'),
			'wrong_after_date' => __('End date is earlier than the start date.', 'events-maker')
		);

		$this->metaboxes = apply_filters(
			'em_event_metaboxes',
			array(
				'event-options-box' => array(
					'title' => __('Event Display Options', 'events-maker'),
					'callback' => array(&$this, 'event_options_cb'),
					'post_type' => 'event',
					'context' => 'side',
					'priority' => 'core'
				),
				'event-date-time-box' => array(
					'title' => __('Event Date and Time', 'events-maker'),
					'callback' => array(&$this, 'event_date_time_cb'),
					'post_type' => 'event',
					'context' => 'normal',
					'priority' => 'high'
				),
				'event-cost-tickets-box' => array(
					'title' => __('Event Tickets', 'events-maker'),
					'callback' => array(&$this, 'event_tickets_cb'),
					'post_type' => 'event',
					'context' => 'normal',
					'priority' => 'high'
				)
			)
		);
	}


	/**
	 * 
	*/
	public function admin_scripts_styles($page)
	{
		$screen = get_current_screen();

		if(($page === 'post-new.php' || $page === 'post.php') && $screen->post_type === 'event')
		{
			global $wp_locale;

			wp_register_script(
				'events-maker-datetimepicker',
				EVENTS_MAKER_URL.'/assets/jquery-timepicker-addon/jquery-ui-timepicker-addon.js',
				array('jquery')
			);

			$path = '';
			$lang = str_replace('_', '-', get_locale());
			$lang_exp = explode('-', $lang);

			if(file_exists(EVENTS_MAKER_PATH.'assets/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang.'.js'))
				$path = EVENTS_MAKER_URL.'/assets/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang.'.js';
			elseif(file_exists(EVENTS_MAKER_PATH.'assets/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang_exp[0].'.js'))
				$path = EVENTS_MAKER_URL.'/assets/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang_exp[0].'.js';
			
			wp_register_script(
				'events-maker-admin-post',
				EVENTS_MAKER_URL.'/js/admin-post.js',
				array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider', 'events-maker-datetimepicker')
			);
			wp_enqueue_script('events-maker-admin-post');
			
			if($path !== '')
			{
				wp_register_script(
					'events-maker-datetimepicker-localization',
					$path,
					array('jquery', 'events-maker-datetimepicker')
				);
				wp_enqueue_script('events-maker-datetimepicker-localization');
			}

			wp_localize_script(
				'events-maker-admin-post',
				'emPostArgs',
				array(
					'ticketsFields' => $this->tickets_fields,
					'ticketDelete' => __('Delete', 'events-maker'),
					'currencySymbol' => em_get_currency_symbol(),
					'startDateTime' => __('Start date/time', 'events-maker'),
					'endDateTime' => __('End date/time', 'events-maker'),
					'dateDelete' => __('Delete', 'events-maker'),
					'deleteTicket' => __('Are you sure you want to delete this ticket?', 'events-maker'),
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

			wp_register_style(
				'events-maker-datetimepicker',
				EVENTS_MAKER_URL.'/assets/jquery-timepicker-addon/jquery-ui-timepicker-addon.css'
			);
			wp_enqueue_style('events-maker-datetimepicker');
		}
	}


	/**
	 * 
	*/
	public function add_events_meta_boxes($post)
	{
		global $wp_meta_boxes;

		foreach($this->metaboxes as $id => $metabox)
		{
			if($id === 'event-cost-tickets-box' && $this->options['general']['use_event_tickets'] === FALSE)
				continue;
			else
				add_meta_box($id, $metabox['title'], $metabox['callback'], $metabox['post_type'], $metabox['context'], $metabox['priority']);
		}

		$found_priority = FALSE;

		foreach(array('low', 'core', 'high') as $priority)
		{
			if(isset($wp_meta_boxes[$post->post_type]['side'][$priority]['postimagediv']))
			{
				$found_priority = $priority;
				$post_image_box = $wp_meta_boxes[$post->post_type]['side'][$priority]['postimagediv'];
				break;
			}
		}

		$sideboxes = array();
		$event_options_box = $wp_meta_boxes[$post->post_type]['side']['core']['event-options-box'];

		unset($wp_meta_boxes[$post->post_type]['side']['core']['event-options-box']);

		if($found_priority !== FALSE)
			unset($wp_meta_boxes[$post->post_type]['side'][$found_priority]['postimagediv']);

		foreach($wp_meta_boxes[$post->post_type]['side']['core'] as $id => $sidebox)
		{
			$sideboxes[$id] = $sidebox;

			if($id === 'submitdiv')
			{
				$sideboxes['event-options-box'] = $event_options_box;

				if($found_priority !== FALSE)
					$sideboxes['postimagediv'] = $post_image_box;
			}
		}

		$wp_meta_boxes[$post->post_type]['side']['core'] = $sideboxes;
	}


	/**
	 * 
	*/
	public function event_date_time_cb($post)
	{
		wp_nonce_field('events_maker_save_event_datetime', 'event_nonce_datetime');

		$event_all_day = get_post_meta($post->ID, '_event_all_day', TRUE);
		$event_start_date = explode(' ', get_post_meta($post->ID, '_event_start_date', TRUE));
		$event_end_date = explode(' ', get_post_meta($post->ID, '_event_end_date', TRUE));

		$html = '
		<div>
			<label for="event_start_date">'.__('Start date/time', 'events-maker').':</label> <input id="event_start_date" type="text" name="event_start_date" value="'.esc_attr($event_start_date[0]).'" /> <input id="event_start_time" type="text" name="event_start_time" value="'.esc_attr(isset($event_start_date[1]) ? substr($event_start_date[1], 0, 5) : '').'" '.($event_all_day === '1' ? 'style="display: none;"' : '').' />
		</div>
		<div>
			<label for="event_end_date">'.__('End date/time', 'events-maker').':</label> <input id="event_end_date" type="text" name="event_end_date" value="'.esc_attr($event_end_date[0]).'" /> <input id="event_end_time" type="text" name="event_end_time" value="'.esc_attr(isset($event_end_date[1]) ? substr($event_end_date[1], 0, 5) : '').'" '.($event_all_day === '1' ? 'style="display: none;"' : '').' />
		</div>
		<div>
			<label for="event_all_day">'.__('All-day event?', 'events-maker').'</label> <input id="event_all_day" type="checkbox" name="event_all_day" '.checked($event_all_day, 1, FALSE).' />
		</div>';

		echo $html;

		do_action('em_after_metabox_event_datetime');
	}


	/**
	 * 
	*/
	public function event_tickets_cb($post)
	{
		wp_nonce_field('events_maker_save_event_tickets', 'event_nonce_tickets');

		$tickets = get_post_meta($post->ID, '_event_tickets', TRUE);
		$free_event = (($free = get_post_meta($post->ID, '_event_free', TRUE)) === '' ? '1' : $free);
		$html_t = '';
		$symbol = em_get_currency_symbol();

		$html = '
		<p>
			<label for="event_free">'.__('Is this a free event?', 'events-maker').'</label>
			<input id="event_free" type="checkbox" name="event_free" '.checked($free_event, '1', FALSE).' /> 
		</p>
		<div id="event_cost_and_tickets"'.($free_event === '1' ? ' style="display: none;"' : '').'>
			<div>
				<a href="#" id="event_add_ticket" class="button button-primary">'.__('Add new ticket', 'events-maker').'</a>
			</div>';

		if(!empty($tickets) && is_array($tickets))
		{
			$id_max = (int)get_post_meta($post->ID, '_event_tickets_last_id', TRUE);

			foreach($tickets as $id => $ticket)
			{
				$html_t .= '
				<p>';

				foreach($this->tickets_fields as $key => $field)
				{
					$html_t .= '
					<label for="event_tickets['.$id.']['.$key.']">'.$field.':</label> <input type="text" id="event_tickets['.$id.']['.$key.']" name="event_tickets['.$id.']['.$key.']" value="'.esc_attr(isset($ticket[$key]) ? $ticket[$key] : '').'" />'.($key === 'price' ? $symbol : '');
				}

				$html_t .= '
					<a href="#" class="event_ticket_delete button button-secondary">'.__('Delete', 'events-maker').'</a>
				</p>';
			}
		}
		else
		{
			$id_max = 0;
			$html_t .= '
				<p>';

			foreach($this->tickets_fields as $key => $field)
			{
				$html_t .= '
					<label for="event_tickets[0]['.$key.']">'.$field.':</label> <input type="text" id="event_tickets[0]['.$key.']" name="event_tickets[0]['.$key.']" value="" />'.($key === 'price' ? $symbol : '');
			}

			$html_t .= '
					<a href="#" class="event_ticket_delete button button-secondary">'.__('Delete', 'events-maker').'</a>
				</p>';
		}

		$html .= '
			<div id="event_tickets" rel="'.$id_max.'">
			'.$html_t.'
			</div>
			<div>
				<label for="event_tickets_url">'.__('Buy tickets URL', 'events-maker').':</label> <input id="event_tickets_url" class="regular-text" type="text" name="event_tickets_url" value="'.esc_url(get_post_meta($post->ID, '_event_tickets_url', TRUE)).'" />
			</div>
		</div>';

		echo $html;

		do_action('em_after_metabox_event_tickets');
		
	}


	/**
	 * 
	*/
	public function event_options_cb($post)
	{
		wp_nonce_field('events_maker_save_event_options', 'event_nonce_options');

		$opts = get_post_meta($post->ID, '_event_display_options', TRUE);
		$html_arr = array();

		$html_arr['display-google-map'] = '
		<div>
			<input id="event_google_map" type="checkbox" name="event_display_options[google_map]" '.checked((isset($opts['google_map']) && $opts['google_map'] !== '' ? $opts['google_map'] : '1'), '1', FALSE).' /> <label for="event_google_map">'.__('Display Google Map', 'events-maker').'</label>
		</div>';

		if($this->options['general']['use_event_tickets'] === TRUE)
		{
			$html_arr['display-tickets-info'] = '
		<div>
			<input id="event_price_tickets_info" type="checkbox" name="event_display_options[price_tickets_info]" '.checked((isset($opts['price_tickets_info']) && $opts['price_tickets_info'] !== '' ? $opts['price_tickets_info'] : '1'), '1', FALSE).' /> <label for="event_price_tickets_info">'.__('Display Tickets Info', 'events-maker').'</label>
		</div>';
		}

		if($this->options['general']['use_organizers'] === TRUE)
		{
			$html_arr['display-organizer-details'] = '
		<div>
			<input id="event_display_organizer_details" type="checkbox" name="event_display_options[display_organizer_details]" '.checked((isset($opts['display_organizer_details']) && $opts['display_organizer_details'] !== '' ? $opts['display_organizer_details'] : '1'), '1', FALSE).' /> <label for="event_display_organizer_details">'.__('Display Organizer Details', 'events-maker').'</label>
		</div>';
		}

		$html_arr['display-location-details'] = '
		<div>
			<input id="event_display_location_details" type="checkbox" name="event_display_options[display_location_details]" '.checked((isset($opts['display_location_details']) && $opts['display_location_details'] !== '' ? $opts['display_location_details'] : '1'), '1', FALSE).' /> <label for="event_display_location_details">'.__('Display Location Details', 'events-maker').'</label>
		</div>';

		foreach(apply_filters('em_metabox_event_options', $html_arr, $opts) as $option)
		{
			echo $option;
		}

		do_action('em_after_metabox_event_options', $opts);
	}


	/**
	 * Saves event with new metaboxes
	*/
	public function save_event($post_ID)
	{
		// break if doing autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return $post_ID;
		
		// verify if event_nonce_datetime nonce is set and valid
		if (!isset($_POST['event_nonce_datetime']) || !wp_verify_nonce($_POST['event_nonce_datetime'], 'events_maker_save_event_datetime'))
        	return $post_ID;
		
		// get tickets use option
		$general_options = get_option('events_maker_general');
		$use_tickets =  $general_options['use_event_tickets'];
		
		// if tickets are not used, don't validate it
		if ($use_tickets === 'yes')
		{
		// verify if event_nonce_tickets nonce is set and valid
		if (!isset($_POST['event_nonce_tickets']) || !wp_verify_nonce($_POST['event_nonce_tickets'], 'events_maker_save_event_tickets'))
        	return $post_ID;
		}
			
		// verify if event_nonce_options nonce is set and valid
		if (!isset($_POST['event_nonce_options']) || !wp_verify_nonce($_POST['event_nonce_options'], 'events_maker_save_event_options'))
        	return $post_ID;

		// break if current user can't edit events
		if (!current_user_can('edit_event', $post_ID))
			return $post_ID;

		$errors = array();

		//event date and time section
		$em_helper = new Events_Maker_Helper();
		$event_all_day = isset($_POST['event_all_day']) ? 1 : 0;
		$start_date_ok = FALSE;

		update_post_meta($post_ID, '_event_all_day', $event_all_day);

		if($event_all_day === 1)
		{
			if(($error = $em_helper->is_valid_date($_POST['event_start_date'])) === TRUE)
			{
				$start_date_ok = TRUE;
				update_post_meta($post_ID, '_event_start_date', $_POST['event_start_date']);
			}
			else
			{
				update_post_meta($post_ID, '_event_start_date', '');
				$errors[] = $this->errors['start_'.$error];
			}

			if(($error = $em_helper->is_valid_date($_POST['event_end_date'])) === TRUE)
			{
				if($start_date_ok === TRUE)
				{
					if($em_helper->is_after_date($_POST['event_start_date'], $_POST['event_end_date']) === TRUE)
						update_post_meta($post_ID, '_event_end_date', $_POST['event_end_date']);
					else
					{
						update_post_meta($post_ID, '_event_end_date', '');
						$errors[] = $this->errors['wrong_after_date'];
					}
				}
				else
					update_post_meta($post_ID, '_event_end_date', $_POST['event_end_date']);
			}
			else
			{
				update_post_meta($post_ID, '_event_end_date', '');
				$errors[] = $this->errors['end_'.$error];
			}
		}
		elseif($event_all_day === 0)
		{
			$error1 = $em_helper->is_valid_date($_POST['event_start_date']);
			$error2 = $em_helper->is_valid_time($_POST['event_start_time']);

			if($error1 === TRUE && $error2 === TRUE)
			{
				$start_date_ok = TRUE;
				update_post_meta($post_ID, '_event_start_date', date('Y-m-d H:i:s', strtotime($_POST['event_start_date'].' '.$_POST['event_start_time'])));
			}
			else
			{
				update_post_meta($post_ID, '_event_start_date', '');

				if($error1 !== TRUE)
					$errors[] = $this->errors['start_'.$error1];

				if($error2 !== TRUE)
					$errors[] = $this->errors['start_'.$error2];
			}

			$error1 = $em_helper->is_valid_date($_POST['event_end_date']);
			$error2 = $em_helper->is_valid_time($_POST['event_end_time']);

			if($error1 === TRUE && $error2 === TRUE)
			{
				if($start_date_ok === TRUE)
				{
					if($em_helper->is_after_date($_POST['event_start_date'].' '.$_POST['event_start_time'], $_POST['event_end_date'].' '.$_POST['event_end_time']) === TRUE)
						update_post_meta($post_ID, '_event_end_date', date('Y-m-d H:i:s', strtotime($_POST['event_end_date'].' '.$_POST['event_end_time'])));
					else
					{
						update_post_meta($post_ID, '_event_end_date', '');
						$errors[] = $this->errors['wrong_after_date'];
					}
				}
				else
					update_post_meta($post_ID, '_event_end_date', date('Y-m-d H:i:s', strtotime($_POST['event_end_date'].' '.$_POST['event_end_time'])));
			}
			else
			{
				update_post_meta($post_ID, '_event_end_date', '');

				if($error1 !== TRUE)
					$errors[] = $this->errors['end_'.$error1];

				if($error2 !== TRUE)
					$errors[] = $this->errors['end_'.$error2];
			}
		}

		//event tickets section
		
		// if tickets are not used, don't save it
		if ($use_tickets === 'yes')
		{
			update_post_meta($post_ID, '_event_free', (isset($_POST['event_free']) ? 1 : 0));
	
			$tickets = $ids = array();
	
			if(isset($_POST['event_free']) === FALSE)
			{
				$last_id = (int)get_post_meta($post_ID, '_event_tickets_last_id', TRUE);
	
				if(isset($_POST['event_tickets']) && is_array($_POST['event_tickets']) && !empty($_POST['event_tickets']))
				{
					foreach($_POST['event_tickets'] as $id => $ticket)
					{
						$tickets_fields = array();
						$empty = 0;
	
						foreach($this->tickets_fields as $key => $trans)
						{
							$tickets_fields[$key] = sanitize_text_field(isset($ticket[$key]) ? $ticket[$key] : '');
							$empty += (($tickets_fields[$key] !== '') ? 1 : 0);
						}
	
						if($empty > 0)
						{
							$ids[] = $id;
							$tickets[$id] = $tickets_fields;
						}
					}
	
					if(empty($tickets))
						$errors[] = $this->errors['empty_tickets'];
	
					if(!empty($ids) && $last_id < ($max = max($ids)))
						update_post_meta($post_ID, '_event_tickets_last_id', $max);
	
					update_post_meta($post_ID, '_event_tickets', $tickets);
				}
				else
					$errors[] = $this->errors['empty_tickets'];
	
				update_post_meta($post_ID, '_event_tickets_url', esc_url($_POST['event_tickets_url']));
			}
			else
			{
				update_post_meta($post_ID, '_event_tickets', $tickets);
				update_post_meta($post_ID, '_event_tickets_url', '');
			}
		}

		//event options section
		$event_display_options = apply_filters('em_event_display_options', array('google_map', 'price_tickets_info', 'display_organizer_details', 'display_location_details'));

		if(is_array($event_display_options) && !empty($event_display_options))
		{
			$event_display_options_arr = array();

			foreach($event_display_options as $event_option)
			{
				$event_display_options_arr[$event_option] = isset($_POST['event_display_options'][$event_option]) ? 1 : 0;
			}

			update_post_meta($post_ID, '_event_display_options', $event_display_options_arr);
		}
		else
			update_post_meta($post_ID, '_event_display_options', array());

		//errors
		if(!empty($errors))
		{
			$html = '';

			foreach($errors as $error)
			{
				$html .= $error.'<br />';
			}

			set_transient($this->transient_id, maybe_serialize(array('status' => 'error', 'text' => $html)), 60);
		}
	}
}
?>