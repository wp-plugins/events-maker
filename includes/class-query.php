<?php
if(!defined('ABSPATH')) exit;

new Events_Maker_Query($events_maker);

class Events_Maker_Query
{
	private $options = array();


	public function __construct($events_maker)
	{
		//settings
		$this->options = $events_maker->get_options();

		//actions
		add_action('init', array(&$this, 'register_rewrite'));
		add_action('pre_get_posts', array(&$this, 'extend_pre_query'));

		//filters
		add_filter('query_vars', array(&$this, 'register_query_vars'));
		add_filter('parse_query', array(&$this, 'filter_events'));
		add_filter('posts_fields', array(&$this, 'posts_fields'), 10, 2);
		add_filter('posts_groupby', array(&$this, 'posts_groupby'), 10, 2);
		add_filter('posts_join', array(&$this, 'posts_join'), 10, 2);
		add_filter('posts_where', array(&$this, 'posts_where'), 10, 2);
		add_filter('posts_orderby', array(&$this, 'posts_orderby'), 10, 2);
		add_filter('request', array(&$this, 'feed_request'));
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

			$post_types = apply_filters('em_event_post_type', array('event'));

			foreach($post_types as $post_type)
			{
				if($pagenow === 'edit.php' && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === $post_type)
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
		$query_vars[] = 'event_ondate';
		$query_vars[] = 'event_show_past_events';
		$query_vars[] = 'event_show_occurrences';

		return $query_vars;
	}


	/**
	 * 
	*/
	public function posts_orderby($orderby, $query)
	{
		if(!empty($query->event_details))
			$orderby = 'events_meta.meta_value '.$query->query_vars['order'].', '.$orderby;

		return $orderby;
	}


	/**
	 * 
	*/
	public function posts_where($where, $query)
	{
		if(!empty($query->event_details))
			$where .= " AND (events_meta.meta_key = '_event_occurrence_date' AND ".implode(' AND ', $query->event_details).")";

		return $where;
	}


	/**
	 * 
	*/
	public function posts_join($join, $query)
	{
		global $wpdb;

		// show occurrences?
		if(
			is_admin()
			||
			(!isset($query->query_vars['event_show_occurrences']) || (isset($query->query_vars['event_show_occurrences']) && !$query->query_vars['event_show_occurrences']))
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

		if(!empty($query->event_details))
			$join .= ' INNER JOIN '.$wpdb->postmeta.' AS events_meta ON ('.$wpdb->prefix.'posts.ID = events_meta.post_id)';

		return $join;
	}


	/**
	 * 
	*/
	public function posts_groupby($groupby, $query)
	{
		global $wpdb;

		// show occurrences?
		if(
			is_admin()
			||
			(!isset($query->query_vars['event_show_occurrences']) || (isset($query->query_vars['event_show_occurrences']) && !$query->query_vars['event_show_occurrences']))
			||
			$query->is_single === true
		)
			return $groupby;

		if(!empty($query->event_details))
			$groupby = 'events_meta.meta_id'.(trim($groupby) !== '' ? ', '.$groupby : '');
		else
			$groupby = $wpdb->postmeta.'.meta_id'.(trim($groupby) !== '' ? ', '.$groupby : '');

		return $groupby;
	}


	function posts_fields($select, $query)
	{
		global $wpdb;

		//todo	dodac tutaj sprawdzenie: $fields = $query->get('fields'); $fields !== 'ids' && $fields !== 'id=>parent'

		// show occurrences?
		if(
			is_admin()
			||
			(!isset($query->query_vars['event_show_occurrences']) || (isset($query->query_vars['event_show_occurrences']) && !$query->query_vars['event_show_occurrences']))
		)
			return $select;

		if(!empty($query->event_details))
			$select .= ", SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS event_occurrence_start_date, SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS event_occurrence_end_date";
		elseif(!is_single())
			$select .= ", SUBSTRING_INDEX(".$wpdb->postmeta.".meta_value, '|', 1) AS event_occurrence_start_date, SUBSTRING_INDEX(".$wpdb->postmeta.".meta_value, '|', -1) AS event_occurrence_end_date";

		return $select;
	}


	/**
	 * 
	*/
	public function extend_pre_query($query)
	{
		if((is_tax('event-location') && isset($query->query_vars['event-location'], $query->query['event-location'])) || (is_tax('event-organizer') && isset($query->query_vars['event-organizer'], $query->query['event-organizer'])) || (is_tax('event-category') && isset($query->query_vars['event-category'], $query->query['event-category'])))
		{
			if(!isset($query->query_vars['event_show_occurrences']))
				$query->query_vars['event_show_occurrences'] = (is_admin() ? false : $this->options['general']['show_occurrences']);

			if($query->query_vars['event_show_occurrences'])
				$keys = array('start' => '_event_occurrence_date', 'end' => '_event_occurrence_date');
			else
				$keys = array('start' => '_event_start_date', 'end' => '_event_end_date');

			$event_order_by = true;

			if(isset($query->query_vars['orderby']))
			{
				if($query->query_vars['orderby'] === 'event_start_date')
				{
					$query->query_vars['meta_key'] = $keys['start'];
					$query->query_vars['orderby'] = 'meta_value';
				}
				elseif($query->query_vars['orderby'] === 'event_end_date')
				{
					$query->query_vars['meta_key'] = $keys['end'];
					$query->query_vars['orderby'] = 'meta_value';
				}
				else
					$event_order_by = false;
			}
			else
			{
				if(in_array($this->options['general']['order_by'], array('start', 'end'), true))
				{
					$query->query_vars['meta_key'] = $keys[$this->options['general']['order_by']];
					$query->query_vars['orderby'] = 'meta_value';
				}
				elseif($this->options['general']['order_by'] === 'publish')
				{
					$query->query_vars['orderby'] = 'date';
					$event_order_by = false;
				}
				else
					$event_order_by = false;
			}

			if(!isset($query->query_vars['order']))
				$query->query_vars['order'] = $this->options['general']['order'];

			if(!isset($query->query_vars['event_show_past_events']) || !is_bool($query->query_vars['event_show_past_events']))
				$query->query_vars['event_show_past_events'] = (is_admin() ? true : $this->options['general']['show_past_events']);

			// some ninja fixes
			if($query->query_vars['event_show_occurrences'] && $query->query_vars['event_show_past_events'] && !$event_order_by)
				$query->query_vars['meta_key'] = $keys['end'];
			elseif($query->query_vars['event_show_occurrences'] && !$query->query_vars['event_show_past_events'] && $event_order_by)
				$query->query_vars['meta_key'] = '';

			$meta_args = $query->get('meta_query');

			if($query->query_vars['event_show_occurrences'])
			{
				global $wpdb;

				$sql = array();

				if(!$query->query_vars['event_show_past_events'] && !$query->is_singular)
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) >= '".current_time('mysql')."'";

				$query->event_details = $sql;
			}
			else
			{
				if(!$query->query_vars['event_show_past_events'] && !$query->is_singular)
				{
					$meta_args[] = array(
						'key' => (!$this->options['general']['expire_current'] ? $keys['end'] : $keys['start']),
						'value' => current_time('mysql'),
						'compare' => '>=',
						'type' => 'DATETIME'
					);
				}
			}

			$query->set('meta_query', $meta_args);
		}

		$post_types = $query->get('post_type');

		// does query contain post type as a string or post types array
		if(is_array($post_types))
			$run_query = (bool)array_intersect($post_types, apply_filters('em_event_post_type', array('event')));
		else
			$run_query = in_array($post_types, apply_filters('em_event_post_type', array('event')));

		if($run_query)
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
				'event_ondate' => '',
				'event_show_past_events' => (is_admin() ? true : $this->options['general']['show_past_events']),
				'event_show_occurrences' => (is_admin() ? true : $this->options['general']['show_occurrences'])
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

			if(!isset($query->query_vars['event_date_range']) || !in_array($query->query_vars['event_date_range'], array('between', 'outside'), true))
				$query->query_vars['event_date_range'] = $defaults['event_date_range'];

			if(!isset($query->query_vars['event_date_type']) || !in_array($query->query_vars['event_date_type'], array('all', 'all_day', 'not_all_day'), true))
				$query->query_vars['event_date_type'] = $defaults['event_date_type'];

			if(!isset($query->query_vars['event_ticket_type']) || !in_array($query->query_vars['event_ticket_type'], array('all', 'free', 'paid'), true))
				$query->query_vars['event_ticket_type'] = $defaults['event_ticket_type'];

			if(!isset($query->query_vars['event_show_past_events']) || !is_bool($query->query_vars['event_show_past_events']))
				$query->query_vars['event_show_past_events'] = $defaults['event_show_past_events'];

			if(!isset($query->query_vars['event_start_after']) || !($format_sa = $em_helper->is_valid_datetime($query->query_vars['event_start_after'])))
				$query->query_vars['event_start_after'] = $defaults['event_start_after'];

			if(!isset($query->query_vars['event_start_before']) || !($format_sb = $em_helper->is_valid_datetime($query->query_vars['event_start_before'])))
				$query->query_vars['event_start_before'] = $defaults['event_start_before'];

			if(!isset($query->query_vars['event_end_after']) || !($format_ea = $em_helper->is_valid_datetime($query->query_vars['event_end_after'])))
				$query->query_vars['event_end_after'] = $defaults['event_end_after'];

			if(!isset($query->query_vars['event_end_before']) || !($format_eb = $em_helper->is_valid_datetime($query->query_vars['event_end_before'])))
				$query->query_vars['event_end_before'] = $defaults['event_end_before'];

			if(!isset($query->query_vars['event_show_occurrences']))
				$query->query_vars['event_show_occurrences'] = (is_admin() ? false : $defaults['event_show_occurrences']);

			if($query->query_vars['event_show_occurrences'])
				$keys = array('start' => '_event_occurrence_date', 'end' => '_event_occurrence_date');
			else
				$keys = array('start' => '_event_start_date', 'end' => '_event_end_date');

			$event_order_by = true;

			if(isset($query->query_vars['orderby']))
			{
				if($query->query_vars['orderby'] === 'event_start_date')
				{
					$query->query_vars['meta_key'] = $keys['start'];
					$query->query_vars['orderby'] = 'meta_value';
				}
				elseif($query->query_vars['orderby'] === 'event_end_date')
				{
					$query->query_vars['meta_key'] = $keys['end'];
					$query->query_vars['orderby'] = 'meta_value';
				}
				else
					$event_order_by = false;
			}
			else
			{
				if(in_array($this->options['general']['order_by'], array('start', 'end'), true))
				{
					$query->query_vars['meta_key'] = $keys[$this->options['general']['order_by']];
					$query->query_vars['orderby'] = 'meta_value';
				}
				elseif($this->options['general']['order_by'] === 'publish')
				{
					$query->query_vars['orderby'] = 'date';
					$event_order_by = false;
				}
				else
					$event_order_by = false;
			}

			if(!isset($query->query_vars['order']))
				$query->query_vars['order'] = $this->options['general']['order'];
			
			// some ninja fixes
			if($query->query_vars['event_show_occurrences'] && $query->query_vars['event_show_past_events'] && !$event_order_by)
				$query->query_vars['meta_key'] = $keys['end'];
			elseif($query->query_vars['event_show_occurrences'] && !$query->query_vars['event_show_past_events'] && $event_order_by)
				$query->query_vars['meta_key'] = '';

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

			if($query->query_vars['event_show_occurrences'])
			{
				global $wpdb;

				$sql = array();

				if(!empty($query->query_vars['event_start_after']))
					$sql[] =  "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS DATETIME) ".($query->query_vars['event_date_range'] === 'between' ? '>=' : '<=')." '".date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_after'].$sa_date))."'";

				if(!empty($query->query_vars['event_start_before']))
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', 1) AS DATETIME) ".($query->query_vars['event_date_range'] === 'between' ? '<=' : '>=')." '".date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_before'].$sb_date))."'";

				if(!empty($query->query_vars['event_end_after']))
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) ".($query->query_vars['event_date_range'] === 'between' ? '>=' : '<=')." '".date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_after'].$ea_date))."'";

				if(!empty($query->query_vars['event_end_before']))
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) ".($query->query_vars['event_date_range'] === 'between' ? '<=' : '>=')." '".date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_before'].$eb_date))."'";

				if(!$query->query_vars['event_show_past_events'] && !$query->is_singular)
					$sql[] = "CAST(SUBSTRING_INDEX(events_meta.meta_value, '|', -1) AS DATETIME) >= '".current_time('mysql')."'";

				$query->event_details = $sql;
			}
			else
			{
				if(!empty($query->query_vars['event_start_after']))
				{
					$meta_args[] = array(
						'key' => $keys['start'],
						'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_after'].$sa_date)),
						'compare' => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
						'type' => 'DATETIME'
					);
				}

				if(!empty($query->query_vars['event_start_before']))
				{
					$meta_args[] = array(
						'key' => $keys['start'],
						'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_start_before'].$sb_date)),
						'compare' => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
						'type' => 'DATETIME'
					);
				}

				if(!empty($query->query_vars['event_end_after']))
				{
					$meta_args[] = array(
						'key' => $keys['end'],
						'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_after'].$ea_date)),
						'compare' => ($query->query_vars['event_date_range'] === 'between' ? '>=' : '<='),
						'type' => 'DATETIME'
					);
				}

				if(!empty($query->query_vars['event_end_before']))
				{
					$meta_args[] = array(
						'key' => $keys['end'],
						'value' => date('Y-m-d H:i:s', strtotime($query->query_vars['event_end_before'].$eb_date)),
						'compare' => ($query->query_vars['event_date_range'] === 'between' ? '<=' : '>='),
						'type' => 'DATETIME'
					);
				}

				if(!$query->query_vars['event_show_past_events'] && !$query->is_singular)
				{
					$meta_args[] = array(
						'key' => (!$this->options['general']['expire_current'] ? $keys['end'] : $keys['start']),
						'value' => current_time('mysql'),
						'compare' => '>=',
						'type' => 'DATETIME'
					);
				}
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

			$query->set('meta_query', $meta_args);
		}
	}


	/**
	 * 
	*/
	public function feed_request($feeds)
	{
		if(isset($feeds['feed']) && !isset($feeds['post_type']) && $this->options['general']['events_in_rss'] === true)
			$feeds['post_type'] = array('post', 'event');

		return $feeds;
	}
}
?>