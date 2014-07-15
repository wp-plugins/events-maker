<?php
if(!defined('ABSPATH')) exit;

new Events_Maker_Widgets($events_maker);

class Events_Maker_Widgets
{
	private $options = array();


	public function __construct($events_maker)
	{
		//settings
		$this->options = $events_maker->get_options();

		//actions
		add_action('widgets_init', array(&$this, 'register_widgets'));
	}


	/**
	 * 
	*/
	public function register_widgets()
	{
		register_widget('Events_Maker_List_Widget');
		register_widget('Events_Maker_Archive_Widget');
		register_widget('Events_Maker_Calendar_Widget');
		register_widget('Events_Maker_Categories_Widget');
		register_widget('Events_Maker_Locations_Widget');

		if($this->options['general']['use_organizers'] === TRUE)
			register_widget('Events_Maker_Organizers_Widget');
	}
}


class Events_Maker_Archive_Widget extends WP_Widget
{
	private $em_defaults = array();
	private $em_types = array();
	private $em_order_types = array();


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_Archive_Widget',
			__('Events Archives', 'events-maker'),
			array(
				'description' => __('Displays events archives', 'events-maker')
			)
		);

		$this->em_defaults = array(
			'title' => __('Events Archives', 'events-maker'),
			'display_as_dropdown' => FALSE,
			'show_post_count' => TRUE,
			'type' => 'monthly',
			'order' => 'desc',
			'limit' => 0
		);

		$this->em_types = array(
			'monthly' => __('Monthly', 'events-maker'),
			'yearly' => __('Yearly', 'events-maker')
		);

		$this->em_order_types = array(
			'asc' => __('Ascending', 'events-maker'),
			'desc' => __('Descending', 'events-maker')
		);
	}


	public function widget($args, $instance)
	{
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= em_display_event_archives($instance);
		$html .= $args['after_widget'];

		echo $html;
	}


	public function form($instance)
	{
		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<input id="'.$this->get_field_id('display_as_dropdown').'" type="checkbox" name="'.$this->get_field_name('display_as_dropdown').'" value="" '.checked(TRUE, (isset($instance['display_as_dropdown']) ? $instance['display_as_dropdown'] : $this->em_defaults['display_as_dropdown']), FALSE).' /> <label for="'.$this->get_field_id('display_as_dropdown').'">'.__('Display as dropdown', 'events-maker').'</label><br />
			<input id="'.$this->get_field_id('show_post_count').'" type="checkbox" name="'.$this->get_field_name('show_post_count').'" value="" '.checked(TRUE, (isset($instance['show_post_count']) ? $instance['show_post_count'] : $this->em_defaults['show_post_count']), FALSE).' /> <label for="'.$this->get_field_id('show_post_count').'">'.__('Show amount of events', 'events-maker').'</label>
		</p>
		<p>
			<label for="'.$this->get_field_id('type').'">'.__('Display Type', 'events-maker').':</label>
			<select id="'.$this->get_field_id('type').'" name="'.$this->get_field_name('type').'">';

		foreach($this->em_types as $id => $type)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['type']) ? $instance['type'] : $this->em_defaults['type']), FALSE).'>'.$type.'</option>';
		}

		$html .= '
			</select>
		</p>
		<p>
			<label for="'.$this->get_field_id('order').'">'.__('Order', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order').'" name="'.$this->get_field_name('order').'">';

		foreach($this->em_order_types as $id => $order)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order']) ? $instance['order'] : $this->em_defaults['order']), FALSE).'>'.$order.'</option>';
		}

		$html .= '
			</select>
		</p>
			<label for="'.$this->get_field_id('limit').'">'.__('Limit', 'events-maker').':</label> <input id="'.$this->get_field_id('limit').'" type="text" name="'.$this->get_field_name('limit').'" value="'.esc_attr(isset($instance['limit']) ? $instance['limit'] : $this->em_defaults['limit']).'" />
		</p>';

		echo $html;
	}


	public function update($new_instance, $old_instance)
	{
		//checkboxes
		$old_instance['display_as_dropdown'] = (isset($new_instance['display_as_dropdown']) ? TRUE : FALSE);
		$old_instance['show_post_count'] = (isset($new_instance['show_post_count']) ? TRUE : FALSE);

		//title
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);

		//limit
		$old_instance['limit'] = (int)(isset($new_instance['limit']) && (int)$new_instance['limit'] >= 0 ? $new_instance['limit'] : $this->em_defaults['limit']);

		//order
		$old_instance['order'] = (isset($new_instance['order']) && in_array($new_instance['order'], array_keys($this->em_order_types), TRUE) ? $new_instance['order'] : $this->em_defaults['order']);

		//type
		$old_instance['type'] = (isset($new_instance['type']) && in_array($new_instance['type'], array_keys($this->em_types), TRUE) ? $new_instance['type'] : $this->em_defaults['type']);

		return $old_instance;
	}
}


class Events_Maker_Calendar_Widget extends WP_Widget
{
	private $em_options = array();
	private $em_defaults = array();
	private $em_taxonomies = array();
	private $em_css_styles = array();
	private $em_included_widgets = 0;


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_Calendar_Widget',
			__('Events Calendar', 'events-maker'),
			array(
				'description' => __('Displays events calendar', 'events-maker')
			)
		);

		add_action('wp_ajax_nopriv_get-events-widget-calendar-month', array(&$this, 'get_widget_calendar_month'));
		add_action('wp_ajax_get-events-widget-calendar-month', array(&$this, 'get_widget_calendar_month'));

		$this->em_options = array_merge(
			array('general' => get_option('events_maker_general'))
		);

		$this->em_defaults = array(
			'title' => __('Events Calendar', 'events-maker'),
			'show_past_events' => $this->em_options['general']['show_past_events'],
			'highlight_weekends' => TRUE,
			'categories' => 'all',
			'locations' => 'all',
			'organizers' => 'all',
			'css_style' => 'basic'
		);

		$this->em_taxonomies = array(
			'all' => __('all', 'events-maker'),
			'selected' => __('selected', 'events-maker')
		);

		$this->em_css_styles = array(
			'basic' => __('basic', 'news-manager'),
			'dark' => __('dark', 'news-manager'),
			'light' => __('light', 'news-manager'),
			'flat' => __('flat', 'news-manager')
		);
	}


	/**
	 * 
	*/
	public function get_widget_calendar_month()
	{
		if(!empty($_POST['action']) && !empty($_POST['date']) && !empty($_POST['widget_id']) && !empty($_POST['nonce']) && $_POST['action'] === 'get-events-widget-calendar-month' && check_ajax_referer('events-maker-widget-calendar', 'nonce', FALSE))
		{
			$widget_options = $this->get_settings();
			$widget_id = (int)$_POST['widget_id'];

			echo $this->display_calendar($widget_options[$widget_id], $_POST['date'], $this->get_events_days($_POST['date'], $widget_options[$widget_id]), $widget_id, TRUE);
		}

		exit;
	}


	/**
	 * 
	*/
	public function widget($args, $instance)
	{
		if(++$this->em_included_widgets === 1)
		{
			wp_register_script(
				'events-maker-front-widgets-calendar',
				EVENTS_MAKER_URL.'/js/front-widgets.js',
				array('jquery')
			);

			wp_enqueue_script('events-maker-front-widgets-calendar');

			wp_localize_script(
				'events-maker-front-widgets-calendar',
				'emArgs',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('events-maker-widget-calendar')
				)
			);
		}

		$date = date('Y-m', current_time('timestamp'));
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= $this->display_calendar($instance, $date, $this->get_events_days($date, $instance), $this->number);
		$html .= $args['after_widget'];

		echo $html;
	}


	/**
	 * 
	*/
	public function form($instance)
	{
		$category = isset($instance['categories']) ? $instance['categories'] : $this->em_defaults['categories'];
		$location = isset($instance['locations']) ? $instance['locations'] : $this->em_defaults['locations'];

		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<input id="'.$this->get_field_id('show_past_events').'" type="checkbox" name="'.$this->get_field_name('show_past_events').'" value="" '.checked(TRUE, (isset($instance['show_past_events']) ? $instance['show_past_events'] : $this->em_defaults['show_past_events']), FALSE).' /> <label for="'.$this->get_field_id('show_past_events').'">'.__('Show past events', 'events-maker').'</label><br />
			<input id="'.$this->get_field_id('highlight_weekends').'" type="checkbox" name="'.$this->get_field_name('highlight_weekends').'" value="" '.checked(TRUE, (isset($instance['highlight_weekends']) ? $instance['highlight_weekends'] : $this->em_defaults['highlight_weekends']), FALSE).' /> <label for="'.$this->get_field_id('highlight_weekends').'">'.__('Highlight weekends', 'events-maker').'</label>
		</p>
		<p>
			<label>'.__('CSS Style', 'news-manager').':</label>
			<select name="'.$this->get_field_name('css_style').'">';

		foreach($this->em_css_styles as $style => $trans)
		{
			$html .= '
				<option value="'.esc_attr($style).'" '.selected($style, (isset($instance['css_style']) ? $instance['css_style'] : $this->em_defaults['css_style']), FALSE).'>'.$trans.'</option>';
		}

		$html .= '
			</select>
		</p>
		<div class="events-maker-list">
			<label>'.__('Event Categories', 'events-maker').':</label>
			<br />';

		foreach($this->em_taxonomies as $id => $taxonomy)
		{
			$html .= '
			<input class="taxonomy-select-cats" id="'.$this->get_field_id('cat_'.$id).'" name="'.$this->get_field_name('categories').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $category, FALSE).' /><label for="'.$this->get_field_id('cat_'.$id).'">'.$taxonomy.'</label> ';
		}

		$html .= '
			<div class="checkbox-list-cats checkbox-list"'.($category === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-category', 'categories_arr', $instance).'
			</div>
		</div>
		<div class="events-maker-list">
			<label>'.__('Event Locations', 'events-maker').':</label>
			<br />';

		foreach($this->em_taxonomies as $id => $taxonomy)
		{
			$html .= '
			<input class="taxonomy-select-locs" id="'.$this->get_field_id('loc_'.$id).'" name="'.$this->get_field_name('locations').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $location, FALSE).' /><label for="'.$this->get_field_id('loc_'.$id).'">'.$taxonomy.'</label> ';
		}

		$html .= '
			<div class="checkbox-list-locs checkbox-list"'.($location === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-location', 'locations_arr', $instance).'
			</div>
		</div>';

		if($this->em_options['general']['use_organizers'] === TRUE)
		{
			$organizer = isset($instance['organizers']) ? $instance['organizers'] : $this->em_defaults['organizers'];

			$html .= '
		<div class="events-maker-list">
			<label>'.__('Event Organizers', 'events-maker').':</label>
			<br />';

			foreach($this->em_taxonomies as $id => $taxonomy)
			{
				$html .= '
			<input class="taxonomy-select-orgs" id="'.$this->get_field_id('org_'.$id).'" name="'.$this->get_field_name('organizers').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $organizer, FALSE).' /><label for="'.$this->get_field_id('org_'.$id).'">'.$taxonomy.'</label> ';
			}

			$html .= '
			<div class="checkbox-list-orgs checkbox-list"'.($organizer === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-organizer', 'organizers_arr', $instance).'
			</div>
		</div>';
		}

		echo $html;
	}


	/**
	 * 
	*/
	public function update($new_instance, $old_instance)
	{
		//checkboxes
		$old_instance['show_past_events'] = (isset($new_instance['show_past_events']) ? TRUE : FALSE);
		$old_instance['highlight_weekends'] = (isset($new_instance['highlight_weekends']) ? TRUE : FALSE);

		//title
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);

		//taxonomies
		$old_instance['categories'] = (isset($new_instance['categories']) && in_array($new_instance['categories'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['categories'] : $this->em_defaults['categories']);
		$old_instance['locations'] = (isset($new_instance['locations']) && in_array($new_instance['locations'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['locations'] : $this->em_defaults['locations']);

		//css style
		$old_instance['css_style'] = (isset($new_instance['css_style']) && in_array($new_instance['css_style'], array_keys($this->em_css_styles), TRUE) ? $new_instance['css_style'] : $this->em_defaults['css_style']);

		//categories
		if($old_instance['categories'] === 'selected')
		{
			$old_instance['categories_arr'] = array();

			if(isset($new_instance['categories_arr']) && is_array($new_instance['categories_arr']))
			{
				foreach($new_instance['categories_arr'] as $cat_id)
				{
					$old_instance['categories_arr'][] = (int)$cat_id;
				}

				$old_instance['categories_arr'] = array_unique($old_instance['categories_arr'], SORT_NUMERIC);
			}
		}
		else
			$old_instance['categories_arr'] = array();

		//locations
		if($old_instance['locations'] === 'selected')
		{
			$old_instance['locations_arr'] = array();

			if(isset($new_instance['locations_arr']) && is_array($new_instance['locations_arr']))
			{
				foreach($new_instance['locations_arr'] as $cat_id)
				{
					$old_instance['locations_arr'][] = (int)$cat_id;
				}

				$old_instance['locations_arr'] = array_unique($old_instance['locations_arr'], SORT_NUMERIC);
			}
		}
		else
			$old_instance['locations_arr'] = array();

		//organizers
		if($this->em_options['general']['use_organizers'] === TRUE)
		{
			$old_instance['organizers'] = (isset($new_instance['organizers']) && in_array($new_instance['organizers'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['organizers'] : $this->em_defaults['organizers']);

			if($old_instance['organizers'] === 'selected')
			{
				$old_instance['organizers_arr'] = array();

				if(isset($new_instance['organizers_arr']) && is_array($new_instance['organizers_arr']))
				{
					foreach($new_instance['organizers_arr'] as $cat_id)
					{
						$old_instance['organizers_arr'][] = (int)$cat_id;
					}

					$old_instance['organizers_arr'] = array_unique($old_instance['organizers_arr'], SORT_NUMERIC);
				}
			}
			else
				$old_instance['organizers_arr'] = array();
		}

		return $old_instance;
	}


	/**
	 * 
	*/
	private function display_calendar($options, $start_date, $events, $widget_id, $ajax = FALSE)
	{
		global $wp_locale;

		$weekdays = array(1 => 7, 2 => 6, 3 => 5, 4 => 4, 5 => 3, 6 => 2, 7 => 1);
		$date = explode(' ', date('Y m j t', strtotime($start_date.'-02')));
		$month = (int)$date[1] - 1;
		$prev_month = (($a = $month - 1) === -1 ? 11 : $a);
		$prev_month_pad = str_pad($prev_month + 1, 2, '0', STR_PAD_LEFT);
		$next_month = ($month + 1) % 12;
		$next_month_pad = str_pad($next_month + 1, 2, '0', STR_PAD_LEFT);
		$first_day = (($first = date('w', strtotime(date($date[0].'-'.$date[1].'-01')))) === '0' ? 7 : $first);
		$rel = $widget_id.'|';

		//Polylang and WPML compatibility
		if(defined('ICL_LANGUAGE_CODE'))
			$rel .= ICL_LANGUAGE_CODE;

		$html = '
		<div id="events-calendar-'.$widget_id.'" class="events-calendar-widget widget_calendar'.(isset($options['css_style']) && $options['css_style'] !== 'basic' ? ' '.$options['css_style'] : '').'" rel="'.$rel.'" '.($ajax === TRUE ? 'style="display: none;"' : '').'>
			<span class="active-month">'.$wp_locale->get_month($date[1]).' '.$date[0].'</span>
			<table class="nav-days">
				<thead>
					<tr>';

		for($i = 1; $i <= 7; $i++)
		{
			$html .= '
						<th scope="col">'.$wp_locale->get_weekday_initial($wp_locale->get_weekday($i !== 7 ? $i : 0)).'</th>';
		}

		$html .= '
					</tr>
				</thead>
				<tbody>';

		$weeks = ceil(($date[3] - $weekdays[$first_day]) / 7) + 1;
		$now = date_parse(current_time('mysql'));
		$day = $k = 1;

		for($i = 1; $i <= $weeks; $i++)
		{
			$html .= '<tr>';

			for($j = 1; $j <= 7; $j++)
			{
				$td_class = array();
				$real_day = (bool)($k++ >= $first_day && $day <= $date[3]);

				if($real_day === TRUE && in_array($day, $events))
					$td_class[] = 'active';

				if($day === $now['day'] && ($month + 1 === $now['month']) && (int)$date[0] === $now['year'])
					$td_class[] = 'today';

				if($real_day === FALSE)
					$td_class[] = 'pad';

				if($options['highlight_weekends'] === TRUE && $j >= 6 && $j <= 7)
					$td_class[] = 'weekend';

				$html .= '<td'.(!empty($td_class) ? ' class="'.implode(' ', $td_class).'"' : '').'>';

				if($real_day === TRUE)
				{
					$html .= (in_array($day, $events) ? '<a href="'.esc_url(em_get_event_date_link($date[0], $month + 1, $day)).'">'.$day.'</a>' : $day);
					$day++;
				}
				else
					$html .= '&nbsp';

				$html .= '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '
				</tbody>
			</table>
			<table class="nav-months">
				<tr>
					<td class="prev-month" colspan="2">
						<a rel="'.($prev_month === 11 ? ($date[0] - 1) : $date[0]).'-'.$prev_month_pad.'" href="#">&laquo; '.apply_filters('em_calendar_month_name', $wp_locale->get_month($prev_month_pad)).'</a>
					</td>
					<td class="ajax-spinner" colspan="1"><div></div></td>
					<td class="next-month" colspan="2">
						<a rel="'.($next_month === 0 ? ($date[0] + 1) : $date[0]).'-'.$next_month_pad.'" href="#">'.apply_filters('em_calendar_month_name', $wp_locale->get_month($next_month_pad)).' &raquo;</a>
					</td>
				</tr>
			</table>
		</div>';

		return $html;
	}


	/**
	 * 
	*/
	private function get_events_days($date, $options)
	{
		$days = $allevents = $exclude_ids = array();

		$args = array(
			'post_type' => 'event',
			'posts_per_page' => -1,
			'suppress_filters' => FALSE,
			'date_range' => 'between',
			'event_show_past_events' => $options['show_past_events']
		);

		if($options['categories'] === 'selected')
		{
			$args['tax_query'][] = array(
				'taxonomy' => 'event-category',
				'field' => 'id',
				'terms' => $options['categories_arr'],
				'include_children' => FALSE,
				'operator' => 'IN'
			);
		}

		if($options['locations'] === 'selected')
		{
			$args['tax_query'][] = array(
				'taxonomy' => 'event-location',
				'field' => 'id',
				'terms' => $options['locations_arr'],
				'include_children' => FALSE,
				'operator' => 'IN'
			);
		}

		if($options['organizers'] === 'selected')
		{
			$args['tax_query'][] = array(
				'taxonomy' => 'event-organizer',
				'field' => 'id',
				'terms' => $options['organizers_arr'],
				'include_children' => FALSE,
				'operator' => 'IN'
			);
		}

		//Polylang and WPML compatibility
		if(defined('ICL_LANGUAGE_CODE'))
			$args['lang'] = ICL_LANGUAGE_CODE;

		$allevents['start'] = get_posts(
			array_merge(
				$args,
				array(
					'event_start_after' => $date.'-01',
					'event_start_before' => $date.'-'.date('t', strtotime($date.'-02'))
				)
			)
		);

		foreach($allevents['start'] as $event)
		{
			$exclude_ids[] = $event->ID;
		}

		$allevents['end'] = get_posts(
			array_merge(
				$args,
				array(
					'event_end_after' => $date.'-01',
					'event_end_before' => $date.'-'.date('t', strtotime($date.'-02')),
					'post__not_in' => (!empty($exclude_ids) ? $exclude_ids : array())
				)
			)
		);

		foreach($allevents as $id => $events)
		{
			if(!empty($events))
			{
				foreach($events as $event)
				{
					$s_datetime = explode(' ', get_post_meta($event->ID, '_event_start_date', TRUE));
					$s_date = explode('-', $s_datetime[0]);
					$e_datetime = explode(' ', get_post_meta($event->ID, '_event_end_date', TRUE));
					$e_date = explode('-', $e_datetime[0]);

					if(count($s_date) === 3 && count($e_date) === 3)
					{
						//same years and same months
						if($s_date[0] === $e_date[0] && $s_date[1] === $e_date[1])
						{
							for($i = $s_date[2]; $i <= $e_date[2]; $i++)
							{
								$days[] = $i;
							}
						}
						else
						{
							if($id === 'start')
							{
								$no_days = date('t', strtotime($s_datetime[0]));

								for($i = $s_date[2]; $i <= $no_days; $i++)
								{
									$days[] = (int)$i;
								}
							}
							else
							{
								for($i = $e_date[2]; $i >= 1; $i--)
								{
									$days[] = (int)$i;
								}
							}
						}
					}
				}
			}
		}

		return array_unique($days, SORT_NUMERIC);
	}


	/**
	 * 
	*/
	private function display_taxonomy_checkbox_list($taxonomy_name, $name, $instance, $depth = 0, $parent = 0)
	{
		$html = '';
		$array = isset($instance[$name]) ? $instance[$name] : array();
		$terms = get_terms(
			$taxonomy_name,
			array(
				'hide_empty' => FALSE,
				'parent' => $parent
			)
		);

		if(!empty($terms))
		{
			$html .= '
			<ul class="terms-checkbox-list depth-level-'.$depth++.'">';

			foreach($terms as $term)
			{
				$html .= '
				<li>
					<input id="'.$this->get_field_id('chkbxlst_'.$term->term_taxonomy_id).'" type="checkbox" name="'.$this->get_field_name($name).'[]" value="'.esc_attr($term->term_id).'" '.checked(TRUE, in_array($term->term_id, $array), FALSE).' /> <label for="'.$this->get_field_id('chkbxlst_'.$term->term_taxonomy_id).'">'.$term->name.'</label>
					'.$this->display_taxonomy_checkbox_list($taxonomy_name, $name, $instance, $depth, $term->term_id).'
				</li>';
			}

			$html .= '
			</ul>';
		}
		elseif($parent === 0)
			$html = __('No results were found.', 'events-maker');

		return $html;
	}
}


class Events_Maker_List_Widget extends WP_Widget
{
	private $em_options = array();
	private $em_defaults = array();
	private $em_taxonomies = array();
	private $em_orders = array();
	private $em_order_types = array();
	private $em_image_sizes = array();


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_List_Widget',
			__('Events List', 'events-maker'),
			array(
				'description' => __('Displays a list of events', 'events-maker')
			)
		);

		$this->em_options = array_merge(
			array('general' => get_option('events_maker_general'))
		);

		$this->em_defaults = array(
			'title' => __('Events', 'events-maker'),
			'number_of_events' => 5,
			'thumbnail_size' => 'thumbnail',
			'categories' => 'all',
			'locations' => 'all',
			'organizers' => 'all',
			'order_by' => 'start',
			'order' => 'desc',
			'show_past_events' => $this->em_options['general']['show_past_events'],
			'show_event_thumbnail' => TRUE,
			'show_event_excerpt' => FALSE,
			'no_events_message' => __('No Events', 'events-maker'),
			'date_format' => $this->em_options['general']['datetime_format']['date'],
			'time_format' => $this->em_options['general']['datetime_format']['time']
		);

		$this->em_taxonomies = array(
			'all' => __('all', 'events-maker'),
			'selected' => __('selected', 'events-maker')
		);

		$this->em_orders = array(
			'start' => __('Start date', 'events-maker'),
			'end' => __('End date', 'events-maker'),
			'publish' => __('Publish date', 'events-maker'),
			'title' => __('Title', 'events-maker')
		);

		$this->em_order_types = array(
			'asc' => __('Ascending', 'events-maker'),
			'desc' => __('Descending', 'events-maker')
		);

		$this->em_image_sizes = array_merge(array('full'), get_intermediate_image_sizes());
		sort($this->em_image_sizes, SORT_STRING);
	}


	/**
	 * 
	*/
	public function widget($args, $instance)
	{
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		//backward compatibility
		$comp = $instance;
		$comp['categories'] = ($instance['categories'] === 'selected' ? $instance['categories_arr'] : array());
		$comp['locations'] = ($instance['locations'] === 'selected' ? $instance['locations_arr'] : array());
		$comp['organizers'] = ($instance['organizers'] === 'selected' ? $instance['organizers_arr'] : array());

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= em_display_events($comp);
		$html .= $args['after_widget'];

		echo $html;
	}


	/**
	 * 
	*/
	public function form($instance)
	{
		$category = isset($instance['categories']) ? $instance['categories'] : $this->em_defaults['categories'];
		$location = isset($instance['locations']) ? $instance['locations'] : $this->em_defaults['locations'];

		if($this->em_options['general']['use_organizers'] === TRUE)
			$organizer = isset($instance['organizers']) ? $instance['organizers'] : $this->em_defaults['organizers'];

		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<label for="'.$this->get_field_id('number_of_events').'">'.__('Number of events', 'events-maker').':</label>
			<input id="'.$this->get_field_id('number_of_events').'" name="'.$this->get_field_name('number_of_events').'" type="text" value="'.esc_attr(isset($instance['number_of_events']) ? $instance['number_of_events'] : $this->em_defaults['number_of_events']).'" />
		</p>
		<div class="events-maker-list">
			<label>'.__('Event Categories', 'events-maker').':</label>
			<br />';

		foreach($this->em_taxonomies as $id => $taxonomy)
		{
			$html .= '
			<input class="taxonomy-select-cats" id="'.$this->get_field_id('cat_'.$id).'" name="'.$this->get_field_name('categories').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $category, FALSE).' /><label for="'.$this->get_field_id('cat_'.$id).'">'.$taxonomy.'</label> ';
		}

		$html .= '
			<div class="checkbox-list-cats checkbox-list"'.($category === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-category', 'categories_arr', $instance).'
			</div>
		</div>
		<div class="events-maker-list">
			<label>'.__('Event Locations', 'events-maker').':</label>
			<br />';

		foreach($this->em_taxonomies as $id => $taxonomy)
		{
			$html .= '
			<input class="taxonomy-select-locs" id="'.$this->get_field_id('loc_'.$id).'" name="'.$this->get_field_name('locations').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $location, FALSE).' /><label for="'.$this->get_field_id('loc_'.$id).'">'.$taxonomy.'</label> ';
		}

		$html .= '
			<div class="checkbox-list-locs checkbox-list"'.($location === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-location', 'locations_arr', $instance).'
			</div>
		</div>';

		if($this->em_options['general']['use_organizers'] === TRUE)
		{
			$html .= '
		<div class="events-maker-list">
			<label>'.__('Event Organizers', 'events-maker').':</label>
			<br />';

			foreach($this->em_taxonomies as $id => $taxonomy)
			{
				$html .= '
			<input class="taxonomy-select-orgs" id="'.$this->get_field_id('org_'.$id).'" name="'.$this->get_field_name('organizers').'" type="radio" value="'.esc_attr($id).'" '.checked($id, $organizer, FALSE).' /><label for="'.$this->get_field_id('org_'.$id).'">'.$taxonomy.'</label> ';
			}

			$html .= '
			<div class="checkbox-list-orgs checkbox-list"'.($organizer === 'all' ? ' style="display: none;"' : '').'>
				'.$this->display_taxonomy_checkbox_list('event-organizer', 'organizers_arr', $instance).'
			</div>
		</div>';
		}

		$html .= '
		<p>
			<label for="'.$this->get_field_id('order_by').'">'.__('Order by', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order_by').'" name="'.$this->get_field_name('order_by').'">';

		foreach($this->em_orders as $id => $order_by)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order_by']) ? $instance['order_by'] : $this->em_defaults['order_by']), FALSE).'>'.$order_by.'</option>';
		}

		$html .= '
			</select>
			<br />
			<label for="'.$this->get_field_id('order').'">'.__('Order', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order').'" name="'.$this->get_field_name('order').'">';

		foreach($this->em_order_types as $id => $order)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order']) ? $instance['order'] : $this->em_defaults['order']), FALSE).'>'.$order.'</option>';
		}

		$show_event_thumbnail = (isset($instance['show_event_thumbnail']) ? $instance['show_event_thumbnail'] : $this->em_defaults['show_event_thumbnail']);

		$html .= '
			</select>
		</p>
		<p>
			<input id="'.$this->get_field_id('show_past_events').'" type="checkbox" name="'.$this->get_field_name('show_past_events').'" value="" '.checked(TRUE, (isset($instance['show_past_events']) ? $instance['show_past_events'] : $this->em_defaults['show_past_events']), FALSE).' /> <label for="'.$this->get_field_id('show_past_events').'">'.__('Display past events', 'events-maker').'</label>
			<br />
			<input id="'.$this->get_field_id('show_event_excerpt').'" type="checkbox" name="'.$this->get_field_name('show_event_excerpt').'" value="" '.checked(TRUE, (isset($instance['show_event_excerpt']) ? $instance['show_event_excerpt'] : $this->em_defaults['show_event_excerpt']), FALSE).' /> <label for="'.$this->get_field_id('show_event_excerpt').'">'.__('Display event excerpt', 'events-maker').'</label>
			<br />
			<input id="'.$this->get_field_id('show_event_thumbnail').'" class="em-show-event-thumbnail" type="checkbox" name="'.$this->get_field_name('show_event_thumbnail').'" value="" '.checked(TRUE, $show_event_thumbnail, FALSE).' /> <label for="'.$this->get_field_id('show_event_thumbnail').'">'.__('Display event thumbnail', 'events-maker').'</label>
		</p>
		<p class="em-event-thumbnail-size"'.($show_event_thumbnail === TRUE ? '' : ' style="display: none;"').'>
			<label for="'.$this->get_field_id('thumbnail_size').'">'.__('Thumbnail size', 'events-maker').':</label>
			<select id="'.$this->get_field_id('thumbnail_size').'" name="'.$this->get_field_name('thumbnail_size').'">';

		$size_type = (isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : $this->em_defaults['thumbnail_size']);

		foreach($this->em_image_sizes as $size)
		{
			$html .= '
				<option value="'.esc_attr($size).'" '.selected($size, $size_type, FALSE).'>'.$size.'</option>';
		}

		$html .= '
			</select>
		</p>
		<p>
			<label for="'.$this->get_field_id('no_events_message').'">'.__('No events message', 'events-maker').':</label>
			<input id="'.$this->get_field_id('no_events_message').'" type="text" name="'.$this->get_field_name('no_events_message').'" value="'.esc_attr(isset($instance['no_events_message']) ? $instance['no_events_message'] : $this->em_defaults['no_events_message']).'" />
		</p>
		<p>
			<label for="'.$this->get_field_id('date_format').'">'.__('Date format', 'events-maker').':</label>
			<input id="'.$this->get_field_id('date_format').'" type="text" name="'.$this->get_field_name('date_format').'" value="'.esc_attr(isset($instance['date_format']) ? $instance['date_format'] : $this->em_defaults['date_format']).'" /><br />
			<label for="'.$this->get_field_id('time_format').'">'.__('Time format', 'events-maker').':</label>
			<input id="'.$this->get_field_id('time_format').'" type="text" name="'.$this->get_field_name('time_format').'" value="'.esc_attr(isset($instance['time_format']) ? $instance['time_format'] : $this->em_defaults['time_format']).'" />
		</p>';

		echo $html;
	}


	/**
	 * 
	*/
	public function update($new_instance, $old_instance)
	{
		//number of events
		$old_instance['number_of_events'] = (int)(isset($new_instance['number_of_events']) ? $new_instance['number_of_events'] : $this->em_defaults['number_of_events']);

		//order
		$old_instance['order_by'] = (isset($new_instance['order_by']) && in_array($new_instance['order_by'], array_keys($this->em_orders), TRUE) ? $new_instance['order_by'] : $this->em_defaults['order_by']);
		$old_instance['order'] = (isset($new_instance['order']) && in_array($new_instance['order'], array_keys($this->em_order_types), TRUE) ? $new_instance['order'] : $this->em_defaults['order']);

		//thumbnail size
		$old_instance['thumbnail_size'] = (isset($new_instance['thumbnail_size']) && in_array($new_instance['thumbnail_size'], $this->em_image_sizes, TRUE) ? $new_instance['thumbnail_size'] : $this->em_defaults['thumbnail_size']);

		//booleans
		$old_instance['show_past_events'] = (isset($new_instance['show_past_events']) ? TRUE : FALSE);
		$old_instance['show_event_thumbnail'] = (isset($new_instance['show_event_thumbnail']) ? TRUE : FALSE);
		$old_instance['show_event_excerpt'] = (isset($new_instance['show_event_excerpt']) ? TRUE : FALSE);

		//texts
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);
		$old_instance['no_events_message'] = sanitize_text_field(isset($new_instance['no_events_message']) ? $new_instance['no_events_message'] : $this->em_defaults['no_events_message']);

		//date format
		$old_instance['date_format'] = sanitize_text_field(isset($new_instance['date_format']) ? $new_instance['date_format'] : $this->em_defaults['date_format']);
		$old_instance['time_format'] = sanitize_text_field(isset($new_instance['time_format']) ? $new_instance['time_format'] : $this->em_defaults['time_format']);

		//taxonomies
		$old_instance['categories'] = (isset($new_instance['categories']) && in_array($new_instance['categories'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['categories'] : $this->em_defaults['categories']);
		$old_instance['locations'] = (isset($new_instance['locations']) && in_array($new_instance['locations'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['locations'] : $this->em_defaults['locations']);

		//categories
		if($old_instance['categories'] === 'selected')
		{
			$old_instance['categories_arr'] = array();

			if(isset($new_instance['categories_arr']) && is_array($new_instance['categories_arr']))
			{
				foreach($new_instance['categories_arr'] as $cat_id)
				{
					$old_instance['categories_arr'][] = (int)$cat_id;
				}

				$old_instance['categories_arr'] = array_unique($old_instance['categories_arr'], SORT_NUMERIC);
			}
		}
		else
			$old_instance['categories_arr'] = array();

		//locations
		if($old_instance['locations'] === 'selected')
		{
			$old_instance['locations_arr'] = array();

			if(isset($new_instance['locations_arr']) && is_array($new_instance['locations_arr']))
			{
				foreach($new_instance['locations_arr'] as $cat_id)
				{
					$old_instance['locations_arr'][] = (int)$cat_id;
				}

				$old_instance['locations_arr'] = array_unique($old_instance['locations_arr'], SORT_NUMERIC);
			}
		}
		else
			$old_instance['locations_arr'] = array();

		//organizers
		if($this->em_options['general']['use_organizers'] === TRUE)
		{
			$old_instance['organizers'] = (isset($new_instance['organizers']) && in_array($new_instance['organizers'], array_keys($this->em_taxonomies), TRUE) ? $new_instance['organizers'] : $this->em_defaults['organizers']);

			if($old_instance['organizers'] === 'selected')
			{
				$old_instance['organizers_arr'] = array();

				if(isset($new_instance['organizers_arr']) && is_array($new_instance['organizers_arr']))
				{
					foreach($new_instance['organizers_arr'] as $cat_id)
					{
						$old_instance['organizers_arr'][] = (int)$cat_id;
					}

					$old_instance['organizers_arr'] = array_unique($old_instance['organizers_arr'], SORT_NUMERIC);
				}
			}
			else
				$old_instance['organizers_arr'] = array();
		}

		return $old_instance;
	}


	/**
	 * 
	*/
	private function display_taxonomy_checkbox_list($taxonomy_name, $name, $instance, $depth = 0, $parent = 0)
	{
		$html = '';
		$array = isset($instance[$name]) ? $instance[$name] : array();
		$terms = get_terms(
			$taxonomy_name,
			array(
				'hide_empty' => FALSE,
				'parent' => $parent
			)
		);

		if(!empty($terms))
		{
			$html .= '
			<ul class="terms-checkbox-list depth-level-'.$depth++.'">';

			foreach($terms as $term)
			{
				$html .= '
				<li>
					<input id="'.$this->get_field_id('chkbxlst_'.$term->term_taxonomy_id).'" type="checkbox" name="'.$this->get_field_name($name).'[]" value="'.esc_attr($term->term_id).'" '.checked(TRUE, in_array($term->term_id, $array), FALSE).' /> <label for="'.$this->get_field_id('chkbxlst_'.$term->term_taxonomy_id).'">'.$term->name.'</label>
					'.$this->display_taxonomy_checkbox_list($taxonomy_name, $name, $instance, $depth, $term->term_id).'
				</li>';
			}

			$html .= '
			</ul>';
		}
		elseif($parent === 0)
			$html = __('No results were found.', 'events-maker');

		return $html;
	}
}


class Events_Maker_Categories_Widget extends WP_Widget
{
	private $em_defaults = array();
	private $em_orders = array();
	private $em_order_types = array();


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_Categories_Widget',
			__('Events Categories', 'events-maker'),
			array(
				'description' => __('Displays a list of events categories', 'events-maker')
			)
		);

		$this->em_defaults = array(
			'title' => __('Events Categories', 'events-maker'),
			'display_as_dropdown' => FALSE,
			'show_hierarchy' => TRUE,
			'order_by' => 'name',
			'order' => 'asc'
		);

		$this->em_orders = array(
			'id' => __('ID', 'events-maker'),
			'name' => __('Name', 'events-maker')
		);

		$this->em_order_types = array(
			'asc' => __('Ascending', 'events-maker'),
			'desc' => __('Descending', 'events-maker')
		);
	}


	/**
	 * 
	*/
	public function widget($args, $instance)
	{
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= em_display_event_taxonomy('event-category', $instance);
		$html .= $args['after_widget'];

		echo $html;
	}


	/**
	 * 
	*/
	public function form($instance)
	{
		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<input id="'.$this->get_field_id('display_as_dropdown').'" type="checkbox" name="'.$this->get_field_name('display_as_dropdown').'" value="" '.checked(TRUE, (isset($instance['display_as_dropdown']) ? $instance['display_as_dropdown'] : $this->em_defaults['display_as_dropdown']), FALSE).' /> <label for="'.$this->get_field_id('display_as_dropdown').'">'.__('Display as dropdown', 'events-maker').'</label><br />
			<input id="'.$this->get_field_id('show_hierarchy').'" type="checkbox" name="'.$this->get_field_name('show_hierarchy').'" value="" '.checked(TRUE, (isset($instance['show_hierarchy']) ? $instance['show_hierarchy'] : $this->em_defaults['show_hierarchy']), FALSE).' /> <label for="'.$this->get_field_id('show_hierarchy').'">'.__('Show hierarchy', 'events-maker').'</label>
		</p>
		<p>
			<label for="'.$this->get_field_id('order_by').'">'.__('Order by', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order_by').'" name="'.$this->get_field_name('order_by').'">';

		foreach($this->em_orders as $id => $order_by)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order_by']) ? $instance['order_by'] : $this->em_defaults['order_by']), FALSE).'>'.$order_by.'</option>';
		}

		$html .= '
			</select>
			<br />
			<label for="'.$this->get_field_id('order').'">'.__('Order', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order').'" name="'.$this->get_field_name('order').'">';

		foreach($this->em_order_types as $id => $order)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order']) ? $instance['order'] : $this->em_defaults['order']), FALSE).'>'.$order.'</option>';
		}

		$html .= '
			</select>
		</p>';

		echo $html;
	}


	/**
	 * 
	*/
	public function update($new_instance, $old_instance)
	{
		//title
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);

		//checkboxes
		$old_instance['display_as_dropdown'] = (isset($new_instance['display_as_dropdown']) ? TRUE : FALSE);
		$old_instance['show_hierarchy'] = (isset($new_instance['show_hierarchy']) ? TRUE : FALSE);

		//order
		$old_instance['order_by'] = (isset($new_instance['order_by']) && in_array($new_instance['order_by'], array_keys($this->em_orders), TRUE) ? $new_instance['order_by'] : $this->em_defaults['order_by']);
		$old_instance['order'] = (isset($new_instance['order']) && in_array($new_instance['order'], array_keys($this->em_order_types), TRUE) ? $new_instance['order'] : $this->em_defaults['order']);

		return $old_instance;
	}
}


class Events_Maker_Locations_Widget extends WP_Widget
{
	private $em_defaults = array();
	private $em_orders = array();
	private $em_order_types = array();


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_Locations_Widget',
			__('Events Locations', 'events-maker'),
			array(
				'description' => __('Displays a list of events locations', 'events-maker')
			)
		);

		$this->em_defaults = array(
			'title' => __('Events Locations', 'events-maker'),
			'display_as_dropdown' => FALSE,
			'show_hierarchy' => TRUE,
			'order_by' => 'name',
			'order' => 'asc'
		);

		$this->em_orders = array(
			'id' => __('ID', 'events-maker'),
			'name' => __('Name', 'events-maker')
		);

		$this->em_order_types = array(
			'asc' => __('Ascending', 'events-maker'),
			'desc' => __('Descending', 'events-maker')
		);
	}


	/**
	 * 
	*/
	public function widget($args, $instance)
	{
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= em_display_event_taxonomy('event-locations', $instance);
		$html .= $args['after_widget'];

		echo $html;
	}


	/**
	 * 
	*/
	public function form($instance)
	{
		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<input id="'.$this->get_field_id('display_as_dropdown').'" type="checkbox" name="'.$this->get_field_name('display_as_dropdown').'" value="" '.checked(TRUE, (isset($instance['display_as_dropdown']) ? $instance['display_as_dropdown'] : $this->em_defaults['display_as_dropdown']), FALSE).' /> <label for="'.$this->get_field_id('display_as_dropdown').'">'.__('Display as dropdown', 'events-maker').'</label><br />
			<input id="'.$this->get_field_id('show_hierarchy').'" type="checkbox" name="'.$this->get_field_name('show_hierarchy').'" value="" '.checked(TRUE, (isset($instance['show_hierarchy']) ? $instance['show_hierarchy'] : $this->em_defaults['show_hierarchy']), FALSE).' /> <label for="'.$this->get_field_id('show_hierarchy').'">'.__('Show hierarchy', 'events-maker').'</label>
		</p>
		<p>
			<label for="'.$this->get_field_id('order_by').'">'.__('Order by', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order_by').'" name="'.$this->get_field_name('order_by').'">';

		foreach($this->em_orders as $id => $order_by)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order_by']) ? $instance['order_by'] : $this->em_defaults['order_by']), FALSE).'>'.$order_by.'</option>';
		}

		$html .= '
			</select>
			<br />
			<label for="'.$this->get_field_id('order').'">'.__('Order', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order').'" name="'.$this->get_field_name('order').'">';

		foreach($this->em_order_types as $id => $order)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order']) ? $instance['order'] : $this->em_defaults['order']), FALSE).'>'.$order.'</option>';
		}

		$html .= '
			</select>
		</p>';

		echo $html;
	}


	/**
	 * 
	*/
	public function update($new_instance, $old_instance)
	{
		//title
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);

		//checkboxes
		$old_instance['display_as_dropdown'] = (isset($new_instance['display_as_dropdown']) ? TRUE : FALSE);
		$old_instance['show_hierarchy'] = (isset($new_instance['show_hierarchy']) ? TRUE : FALSE);

		//order
		$old_instance['order_by'] = (isset($new_instance['order_by']) && in_array($new_instance['order_by'], array_keys($this->em_orders), TRUE) ? $new_instance['order_by'] : $this->em_defaults['order_by']);
		$old_instance['order'] = (isset($new_instance['order']) && in_array($new_instance['order'], array_keys($this->em_order_types), TRUE) ? $new_instance['order'] : $this->em_defaults['order']);

		return $old_instance;
	}
}


class Events_Maker_Organizers_Widget extends WP_Widget
{
	private $em_defaults = array();
	private $em_orders = array();
	private $em_order_types = array();


	public function __construct()
	{
		parent::__construct(
			'Events_Maker_Organizers_Widget',
			__('Events Organizers', 'events-maker'),
			array(
				'description' => __('Displays a list of events organizers', 'events-maker')
			)
		);

		$this->em_defaults = array(
			'title' => __('Events Organizers', 'events-maker'),
			'display_as_dropdown' => FALSE,
			'show_hierarchy' => TRUE,
			'order_by' => 'name',
			'order' => 'asc'
		);

		$this->em_orders = array(
			'id' => __('ID', 'events-maker'),
			'name' => __('Name', 'events-maker')
		);

		$this->em_order_types = array(
			'asc' => __('Ascending', 'events-maker'),
			'desc' => __('Descending', 'events-maker')
		);
	}


	/**
	 * 
	*/
	public function widget($args, $instance)
	{
		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$html = $args['before_widget'].$args['before_title'].(!empty($instance['title']) ? $instance['title'] : $this->em_defaults['title']).$args['after_title'];
		$html .= em_display_event_taxonomy('event-organizer', $instance);
		$html .= $args['after_widget'];

		echo $html;
	}


	/**
	 * 
	*/
	public function form($instance)
	{
		$html = '
		<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'events-maker').':</label>
			<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr(isset($instance['title']) ? $instance['title'] : $this->em_defaults['title']).'" />
		</p>
		<p>
			<input id="'.$this->get_field_id('display_as_dropdown').'" type="checkbox" name="'.$this->get_field_name('display_as_dropdown').'" value="" '.checked(TRUE, (isset($instance['display_as_dropdown']) ? $instance['display_as_dropdown'] : $this->em_defaults['display_as_dropdown']), FALSE).' /> <label for="'.$this->get_field_id('display_as_dropdown').'">'.__('Display as dropdown', 'events-maker').'</label><br />
			<input id="'.$this->get_field_id('show_hierarchy').'" type="checkbox" name="'.$this->get_field_name('show_hierarchy').'" value="" '.checked(TRUE, (isset($instance['show_hierarchy']) ? $instance['show_hierarchy'] : $this->em_defaults['show_hierarchy']), FALSE).' /> <label for="'.$this->get_field_id('show_hierarchy').'">'.__('Show hierarchy', 'events-maker').'</label>
		</p>
		<p>
			<label for="'.$this->get_field_id('order_by').'">'.__('Order by', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order_by').'" name="'.$this->get_field_name('order_by').'">';

		foreach($this->em_orders as $id => $order_by)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order_by']) ? $instance['order_by'] : $this->em_defaults['order_by']), FALSE).'>'.$order_by.'</option>';
		}

		$html .= '
			</select>
			<br />
			<label for="'.$this->get_field_id('order').'">'.__('Order', 'events-maker').':</label>
			<select id="'.$this->get_field_id('order').'" name="'.$this->get_field_name('order').'">';

		foreach($this->em_order_types as $id => $order)
		{
			$html .= '
				<option value="'.esc_attr($id).'" '.selected($id, (isset($instance['order']) ? $instance['order'] : $this->em_defaults['order']), FALSE).'>'.$order.'</option>';
		}

		$html .= '
			</select>
		</p>';

		echo $html;
	}


	/**
	 * 
	*/
	public function update($new_instance, $old_instance)
	{
		//title
		$old_instance['title'] = sanitize_text_field(isset($new_instance['title']) ? $new_instance['title'] : $this->em_defaults['title']);

		//checkboxes
		$old_instance['display_as_dropdown'] = (isset($new_instance['display_as_dropdown']) ? TRUE : FALSE);
		$old_instance['show_hierarchy'] = (isset($new_instance['show_hierarchy']) ? TRUE : FALSE);

		//order
		$old_instance['order_by'] = (isset($new_instance['order_by']) && in_array($new_instance['order_by'], array_keys($this->em_orders), TRUE) ? $new_instance['order_by'] : $this->em_defaults['order_by']);
		$old_instance['order'] = (isset($new_instance['order']) && in_array($new_instance['order'], array_keys($this->em_order_types), TRUE) ? $new_instance['order'] : $this->em_defaults['order']);

		return $old_instance;
	}
}
?>