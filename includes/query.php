<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Events_Maker_Query
{
	private $options = array();


	public function __construct()
	{
		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general')),
			array('permalinks' => get_option('events_maker_permalinks'))
		);

		//actions
		add_action('init', array(&$this, 'register_rewrite'));
		add_action('pre_get_posts', array(&$this, 'extend_pre_query'));

		//filters
		add_filter('query_vars', array(&$this, 'register_query_vars'));
		add_filter('parse_query', array(&$this, 'filter_events'));
	}


	/**
	 * 
	*/
	public function register_rewrite()
	{
		global $wp_rewrite;

		$wp_rewrite->add_rewrite_tag(
			'%event_ondate%',
			'([0-9]{4}(?:/[0-9]{2}(?:/[0-9]{2})?)?)',
			'post_type=event&event_ondate='
		);

		$wp_rewrite->add_permastruct(
			'event_ondate',
			$this->options['permalinks']['event_rewrite_base'].'/%event_ondate%',
			array(
				'with_front' => FALSE
			)
		);

		if($this->options['general']['rewrite_rules'] === TRUE)
		{
			$this->options['general']['rewrite_rules'] = FALSE;
			update_option('events_maker_general', $this->options['general']);
			flush_rewrite_rules();
		}
	}


	/**
	 * 
	*/
	public function filter_events($query)
	{
		if(is_admin())
		{
			global $pagenow;

			$screen = get_current_screen();

			if($pagenow === 'edit.php' && $screen->post_type == 'event' && $screen->id === 'edit-event')
			{
				$em_helper = new Events_Maker_Helper();
				$meta_args = $query->get('meta_query');
				$start = !empty($_GET['event_start_date']) ? sanitize_text_field($_GET['event_start_date']) : '';
				$end = !empty($_GET['event_end_date']) ? sanitize_text_field($_GET['event_end_date']) : '';
				$sb = $em_helper->is_valid_date($start);
				$eb = $em_helper->is_valid_date($end);

				if($sb === TRUE && $eb === TRUE)
				{
					$meta_args = array(
						array(
							'key' => '_event_start_date',
							'value' => $start,
							'compare' => '>=',
							'type' => 'DATE'
						),
						array(
							'key' => '_event_end_date',
							'value' => $end,
							'compare' => '<=',
							'type' => 'DATE'
						)
					);
				}
				elseif($sb === TRUE && $eb !== TRUE)
				{
					$meta_args = array(
						array(
							'key' => '_event_start_date',
							'value' => $start,
							'compare' => '>=',
							'type' => 'DATE'
						)
					);
				}
				elseif($sb !== TRUE && $eb === TRUE)
				{
					$meta_args = array(
						array(
							'key' => '_event_end_date',
							'value' => $end,
							'compare' => '<=',
							'type' => 'DATE'
						)
					);
				}

				if(!empty($meta_args))
					$query->set('meta_query', $meta_args);
			}
		}
	}


	/**
	 * 
	*/
	public function register_query_vars($query_vars)
	{
		$query_vars[] = 'event_start_after';
		$query_vars[] = 'event_start_before';
		$query_vars[] = 'event_end_after';
		$query_vars[] = 'event_end_before';
		$query_vars[] = 'event_date_range';
		$query_vars[] = 'event_date_type';
		$query_vars[] = 'event_ticket_type';
		$query_vars[] = 'event_categories';
		$query_vars[] = 'event_locations';
		$query_vars[] = 'event_organizers';
		$query_vars[] = 'event_ondate';
		$query_vars[] = 'event_show_past_events';

		return $query_vars;
	}


	/**
	 * 
	*/
	public function extend_pre_query($query)
	{
		if((is_tax('event-location') && isset($query->query_vars['event-location'], $query->query['event-location'])) || (is_tax('event-organizer') && isset($query->query_vars['event-organizer'], $query->query['event-organizer'])) || (is_tax('event-category') && isset($query->query_vars['event-category'], $query->query['event-category'])))
		{
			if(!isset($query->query_vars['orderby']))
			{
				if(in_array($this->options['general']['order_by'], array('start', 'end'), TRUE))
				{
					$query->query_vars['meta_key'] = '_event_'.$this->options['general']['order_by'].'_date';
					$query->query_vars['orderby'] = 'meta_value';
				}
				else
					$query->query_vars['orderby'] = 'date';
			}

			if(!isset($query->query_vars['order']))
			{
				$query->query_vars['order'] = $this->options['general']['order'];
			}
		}

		if($query->get('post_type') === 'event')
		{
			$em_helper = new Events_Maker_Helper();
			$format_sa = $format_sb = $format_ea = $format_eb = '';

			$defaults = array(
				'event_start_after' => '',
				'event_start_before' => '',
				'event_end_after' => '',
				'event_end_before' => '',
				'event_date_range' => 'between',
				'event_date_type' => 'all',
				'event_ticket_type' => 'all',
				'event_categories' => 'all',
				'event_locations' => 'all',
				'event_organizers' => 'all',
				'event_ondate' => '',
				'event_show_past_events' => (is_admin() ? TRUE : $this->options['general']['show_past_events'])
			);

			if(!empty($query->query_vars['event_ondate']))
			{
				$date = explode('/', $query->query_vars['event_ondate']);

				//year
				if(($a = count($date)) === 1)
				{
					$ondate_start = $date[0].'-01-01';
					$ondate_end = $date[0].'-12-31';
				}
				//year + month
				elseif($a === 2)
				{
					$ondate_start = $date[0].'-'.$date[1].'-01';
					$ondate_end = $date[0].'-'.$date[1].'-'.date('t', strtotime($date[0].'-'.$date[1].'-02'));
				}
				//year + month + day
				elseif($a === 3)
					$ondate_start = $ondate_end = $date[0].'-'.$date[1].'-'.$date[2];

				$query->set('event_start_before', $ondate_end);
				$query->set('event_end_after', $ondate_start);
			}
			else $query->query_vars['event_ondate'] = $defaults['event_ondate'];

			if(!isset($query->query_vars['event_date_range']) || !in_array($query->query_vars['event_date_range'], array('between', 'outside'), TRUE))
				$query->query_vars['event_date_range'] = $defaults['event_date_range'];

			if(!isset($query->query_vars['event_date_type']) || !in_array($query->query_vars['event_date_type'], array('all', 'all_day', 'not_all_day'), TRUE))
				$query->query_vars['event_date_type'] = $defaults['event_date_type'];

			if(!isset($query->query_vars['event_ticket_type']) || !in_array($query->query_vars['event_ticket_type'], array('all', 'free', 'paid'), TRUE))
				$query->query_vars['event_ticket_type'] = $defaults['event_ticket_type'];

			if(!isset($query->query_vars['event_show_past_events']) || !is_bool($query->query_vars['event_show_past_events']))
				$query->query_vars['event_show_past_events'] = $defaults['event_show_past_events'];

			if(!isset($query->query_vars['event_start_after']) || ($format_sa = $em_helper->is_valid_datetime($query->query_vars['event_start_after'])) === FALSE)
				$query->query_vars['event_start_after'] = $defaults['event_start_after'];

			if(!isset($query->query_vars['event_start_before']) || ($format_sb = $em_helper->is_valid_datetime($query->query_vars['event_start_before'])) === FALSE)
				$query->query_vars['event_start_before'] = $defaults['event_start_before'];

			if(!isset($query->query_vars['event_end_after']) || ($format_ea = $em_helper->is_valid_datetime($query->query_vars['event_end_after'])) === FALSE)
				$query->query_vars['event_end_after'] = $defaults['event_end_after'];

			if(!isset($query->query_vars['event_end_before']) || ($format_eb = $em_helper->is_valid_datetime($query->query_vars['event_end_before'])) === FALSE)
				$query->query_vars['event_end_before'] = $defaults['event_end_before'];

			if(!isset($query->query_vars['event_categories']) || (is_string($query->query_vars['event_categories']) && $query->query_vars['event_categories'] !== 'all') || (is_array($query->query_vars['event_categories']) && !in_array($query->query_vars['event_categories'][0], array('id', 'slug'), TRUE)))
				$query->query_vars['event_categories'] = $defaults['event_categories'];

			if(!isset($query->query_vars['event_locations']) || (is_string($query->query_vars['event_locations']) && $query->query_vars['event_locations'] !== 'all') || (is_array($query->query_vars['event_locations']) && !in_array($query->query_vars['event_locations'][0], array('id', 'slug'), TRUE)))
				$query->query_vars['event_locations'] = $defaults['event_locations'];

			if(!isset($query->query_vars['event_organizers']) || (is_string($query->query_vars['event_organizers']) && $query->query_vars['event_organizers'] !== 'all') || (is_array($query->query_vars['event_organizers']) && !in_array($query->query_vars['event_organizers'][0], array('id', 'slug'), TRUE)))
				$query->query_vars['event_organizers'] = $defaults['event_organizers'];

			if(!isset($query->query_vars['orderby']))
			{
				if(in_array($this->options['general']['order_by'], array('start', 'end'), TRUE))
				{
					$query->query_vars['meta_key'] = '_event_'.$this->options['general']['order_by'].'_date';
					$query->query_vars['orderby'] = 'meta_value';
				}
				else
					$query->query_vars['orderby'] = 'date';
			}

			if(!isset($query->query_vars['order']))
			{
				$query->query_vars['order'] = $this->options['general']['order'];
			}

			if($format_sa === 'Y-m-d')
				$sa_date = ($query->query_vars['event_date_range'] === 'between' ? ' 00:00:00' : ' 23:59:00');
			elseif($format_sa === 'Y-m-d H:i')
				$sa_date = ':00';
			else
				$sa_date = '';

			if($format_sb === 'Y-m-d')
				$sb_date = ($query->query_vars['event_date_range'] === 'between' ? ' 23:59:00' : ' 00:00:00');
			elseif($format_sb === 'Y-m-d H:i')
				$sb_date = ':00';
			else
				$sb_date = '';

			if($format_ea === 'Y-m-d')
				$ea_date = ($query->query_vars['event_date_range'] === 'between' ? ' 00:00:00' : ' 23:59:00');
			elseif($format_ea === 'Y-m-d H:i')
				$ea_date = ':00';
			else
				$ea_date = '';

			if($format_eb === 'Y-m-d')
				$eb_date = ($query->query_vars['event_date_range'] === 'between' ? ' 23:59:00' : ' 00:00:00');
			elseif($format_eb === 'Y-m-d H:i')
				$eb_date = ':00';
			else
				$eb_date = '';

			$meta_args = $query->get('meta_query');
			$tax_args = $query->get('tax_query');

			if(!empty($query->query_vars['event_start_after']))
			{
				$meta_args[] = array(
					'key' => '_event_start_date',
					'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_after'].$sa_date)),
					'compare' => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
					'type' => 'DATETIME'
				);
			}

			if(!empty($query->query_vars['event_start_before']))
			{
				$meta_args[] = array(
					'key' => '_event_start_date',
					'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_before'].$sb_date)),
					'compare' => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
					'type' => 'DATETIME'
				);
			}

			if(!empty($query->query_vars['event_end_after']))
			{
				$meta_args[] = array(
					'key' => '_event_end_date',
					'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_after'].$ea_date)),
					'compare' => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
					'type' => 'DATETIME'
				);
			}

			if(!empty($query->query_vars['event_end_before']))
			{
				$meta_args[] = array(
					'key' => '_event_end_date',
					'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_before'].$eb_date)),
					'compare' => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
					'type' => 'DATETIME'
				);
			}

			if(is_array($query->query_vars['event_categories']) && is_array($query->query_vars['event_categories'][1]) && !empty($query->query_vars['event_categories'][1]))
			{
				if($query->query_vars['event_categories'][0] === 'id')
				{
					$cats = array();

					foreach($query->query_vars['event_categories'][1] as $id)
					{
						$cats[] = (int)$id;
					}

					$cats = array_unique($cats, SORT_NUMERIC);
				}
				else
					$cats = array_unique($query->query_vars['event_categories'][1]);

				$tax_args[] = array(
					'taxonomy' => 'event-category',
					'field' => $query->query_vars['event_categories'][0],
					'terms' => $cats,
					'include_children' => FALSE,
					'operator' => 'IN'
				);
			}

			if(is_array($query->query_vars['event_locations']) && is_array($query->query_vars['event_locations'][1]) && !empty($query->query_vars['event_locations'][1]))
			{
				if($query->query_vars['event_locations'][0] === 'id')
				{
					$locs = array();

					foreach($query->query_vars['event_locations'][1] as $id)
					{
						$locs[] = (int)$id;
					}

					$locs = array_unique($locs, SORT_NUMERIC);
				}
				else
					$locs = array_unique($query->query_vars['event_locations'][1]);

				$tax_args[] = array(
					'taxonomy' => 'event-location',
					'field' => $query->query_vars['event_locations'][0],
					'terms' => $locs,
					'include_children' => FALSE,
					'operator' => 'IN'
				);
			}

			if(is_array($query->query_vars['event_organizers']) && is_array($query->query_vars['event_organizers'][1]) && !empty($query->query_vars['event_organizers'][1]))
			{
				if($query->query_vars['event_organizers'][0] === 'id')
				{
					$orgs = array();

					foreach($query->query_vars['event_organizers'][1] as $id)
					{
						$orgs[] = (int)$id;
					}

					$orgs = array_unique($orgs, SORT_NUMERIC);
				}
				else
					$orgs = array_unique($query->query_vars['event_organizers'][1]);

				$tax_args[] = array(
					'taxonomy' => 'event-organizer',
					'field' => $query->query_vars['event_organizers'][0],
					'terms' => $orgs,
					'include_children' => FALSE,
					'operator' => 'IN'
				);
			}

			if($query->query_vars['event_date_type'] === 'all_day')
			{
				$meta_args[] = array(
					'key' => '_event_all_day',
					'value' => 1,
					'compare' => '=',
					'type' => 'NUMERIC'
				);
			}
			elseif($query->query_vars['event_date_type'] === 'not_all_day')
			{
				$meta_args[] = array(
					'key' => '_event_all_day',
					'value' => 0,
					'compare' => '=',
					'type' => 'NUMERIC'
				);
			}

			if($query->query_vars['event_ticket_type'] !== 'all')
			{
				$meta_args[] = array(
					'key' => '_event_free',
					'value' => ($query->query_vars['event_ticket_type'] === 'free' ? 1 : 0),
					'compare' => '=',
					'type' => 'NUMERIC'
				);
			}

			if($query->query_vars['event_show_past_events'] === FALSE && $query->is_singular() !== TRUE)
			{
				$meta_args[] = array(
					'key' => ($this->options['general']['expire_current'] === FALSE ? '_event_end_date' : '_event_start_date'),
					'value' => current_time('mysql'),
					'compare' => '>=',
					'type' => 'DATETIME'
				);
			}

			$query->set('meta_query', $meta_args);
			$query->set('tax_query', $tax_args);
		}
	}
}

$events_maker_query = new Events_Maker_Query();

?>