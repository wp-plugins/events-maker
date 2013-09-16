<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Events_Maker_Listing
{
	private $options = array();


	public function __construct()
	{
		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general'))
		);

		//actions
		add_action('manage_posts_custom_column', array(&$this, 'add_new_event_columns_content'), 10, 2);
		add_action('restrict_manage_posts', array(&$this, 'event_filter_dates'));

		//filters
		add_filter('manage_edit-event_sortable_columns', array(&$this, 'register_sortable_custom_columns'));
		add_filter('request', array(&$this, 'sort_custom_columns'));
		add_filter('manage_event_posts_columns', array(&$this, 'add_new_event_columns'));
	}


	/**
	 * 
	*/
	public function event_filter_dates()
	{
		if(is_admin())
		{
			global $pagenow;

			$screen = get_current_screen();

			if($pagenow === 'edit.php' && $screen->post_type == 'event' && $screen->id === 'edit-event')
			{
				echo '
				<label for="emflds">'.__('Start Date', 'events-maker').'</label> <input id="emflds" class="events-datepicker" type="text" name="event_start_date" value="'.(!empty($_GET['event_start_date']) ? esc_attr($_GET['event_start_date']) : '').'" /> 
				<label for="emflde">'.__('End Date', 'events-maker').'</label> <input id="emflde" class="events-datepicker" type="text" name="event_end_date" value="'.(!empty($_GET['event_end_date']) ? esc_attr($_GET['event_end_date']) : '').'" /> ';
			}
		}
	}


	/**
	 * Registers sortable columns
	*/
	public function register_sortable_custom_columns($column)
	{
		$column['event_start_date'] = 'event_start_date';
		$column['event_end_date'] = 'event_end_date';

		return $column;
	}


	/**
	 * Sorts custom columns
	*/
	public function sort_custom_columns($qvars)
	{
		if(is_admin() && $qvars['post_type'] === 'event')
		{
			if(!isset($qvars['orderby']))
			{
				switch($this->options['general']['order_by'])
				{
					case 'start':
						$qvars['orderby'] = 'event_start_date';
						break;

					case 'end':
						$qvars['orderby'] = 'event_end_date';
						break;

					case 'publish':
					default:
						$qvars['orderby'] = 'date';
						break;
				}
			}

			if(isset($qvars['orderby']))
			{
				if(in_array($qvars['orderby'], array('event_start_date', 'event_end_date'), TRUE))
				{
					$qvars['meta_key'] = '_'.$qvars['orderby'];
					$qvars['orderby'] = 'meta_value';
				}
				elseif($qvars['orderby'] === 'date')
					$qvars['orderby'] = 'date';
			}

			if(!isset($qvars['order']))
				$qvars['order'] = $this->options['general']['order'];
		}

		return $qvars;
	}


	/**
	 * Adds new event listing columns
	*/
	public function add_new_event_columns($columns)
	{
		unset($columns['date']);

		$columns['event_start_date'] = __('Start', 'events-maker');
		$columns['event_end_date'] = __('End', 'events-maker');

		if($this->options['general']['use_event_tickets'] === TRUE)
			$columns['event_free'] = __('Tickets', 'events-maker');

		return $columns;
	}


	/**
	 * Adds new event listing columns content
	*/
	public function add_new_event_columns_content($column_name, $id)
	{
		$mode = !empty($_GET['mode']) ? sanitize_text_field($_GET['mode']) : '';

		switch($column_name)
		{
			case 'event_start_date':
			case 'event_end_date':
				echo substr(str_replace(' ', ', ', get_post_meta($id, '_'.$column_name, TRUE)), 0, 17);
				break;

			case 'event_free':
				if(em_is_free($id) === FALSE)
				{
					echo __('Paid', 'events-maker').'<br />';

					if($mode === 'excerpt')
					{
						$tickets = get_post_meta($id, '_event_tickets', TRUE);

						foreach($tickets as $ticket)
						{
							echo $ticket['name'].': '.em_get_currency_symbol($ticket['price']).'<br />';
						}
					}
				}
				else
					echo __('Free', 'events-maker');
				break;
		}
	}
}

$events_maker_listing = new Events_Maker_Listing();

?>