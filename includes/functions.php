<?php
if(!defined('ABSPATH')) exit; //exit if accessed directly


function em_get_events($args = array())
{
	$defaults = array(
		'post_type' => 'event',
		'suppress_filters' => FALSE,
		'posts_per_page' => -1
	);

	return get_posts(array_merge($args, $defaults));
}


function em_get_event($post_id = 0)
{
	return (($post = get_post((int)$post_id, 'OBJECT', 'raw')) !== NULL ? $post : NULL);
}


function em_get_tickets($post_id = 0)
{
	if(em_is_free($post_id) === FALSE)
	{
		$tickets = get_post_meta((int)$post_id, '_event_tickets', TRUE);

		if(isset($tickets) && is_array($tickets))
			return $tickets;
		else
			return NULL;
	}
	else
		return FALSE;
}


function em_get_currency_symbol($price = '')
{
	$options = get_option('events_maker_general');

	$symbol = ($options['currencies']['symbol'] === '' ? mb_strtoupper($options['currencies']['code']) : $options['currencies']['symbol']);

	if(is_numeric($price))
	{
		switch($options['currencies']['format'])
		{
			case 1:
				$price = number_format($price, 2, '.', ',');
				break;

			case 2:
				$price = number_format($price, 0, '', ',');
				break;

			case 3:
				$price = number_format($price, 0, '', '');
				break;

			case 4:
				$price = number_format($price, 2, '.', '');
				break;

			case 5:
				$price = number_format($price, 2, ',', ' ');
				break;

			case 6:
				$price = number_format($price, 2, '.', ' ');
				break;
		}

		return ($options['currencies']['position'] === 'after' ? $price.$symbol : $symbol.$price);
	}
	else
		return $symbol;
}


function em_is_all_day($post_id = 0)
{
	if(get_post_type($post_id) === 'event')
		return (get_post_meta((int)$post_id, '_event_all_day', TRUE) === '1' ? TRUE : FALSE);
	else
		return NULL;
}


function em_is_free($post_id = 0)
{
	if(get_post_type($post_id) === 'event')
		return (get_post_meta((int)$post_id, '_event_free', TRUE) === '1' ? TRUE : FALSE);
	else
		return NULL;
}


function em_get_locations($args = array('fields' => 'all'))
{
	if(taxonomy_exists('event-location'))
	{
		$locations = get_terms('event-location', $args);

		if(isset($args['fields']) && $args['fields'] === 'all')
		{
			foreach($locations as $id => $location)
			{
				$locations[$id]->location_meta = get_option('event_location_'.$location->term_id);
			}
		}

		return $locations;
	}
	else
		return FALSE;
}


function em_get_location($term_id = NULL)
{
	if(taxonomy_exists('event-location'))
	{
		if($term_id === NULL)
		{
			$term = get_queried_object();

			if(is_tax() && is_object($term) && isset($term->term_id))
				$term_id = $term->term_id;
			else
				return NULL;
		}

		if(($location = get_term((int)$term_id, 'event-location', 'OBJECT', 'raw')) !== NULL)
		{
			$location->location_meta = get_option('event_location_'.$location->term_id);

			return $location;
		}
		else
			return NULL;
	}
	else
		return FALSE;
}


function em_get_locations_for($post_id = 0)
{
	if(taxonomy_exists('event-location'))
	{
		if(is_array($locations = wp_get_post_terms((int)$post_id, 'event-location')))
		{
			if(!empty($locations))
			{
				foreach($locations as $id => $location)
				{
					$locations[$id]->location_meta = get_option('event_location_'.$location->term_id);
				}

				return $locations;
			}
			else
				return array();
		}
		else
			return NULL;
	}
	else
		return FALSE;
}


function em_get_organizers($args = array('fields' => 'all'))
{
	if(taxonomy_exists('event-organizer'))
	{
		$organizers = get_terms('event-organizer', $args);

		if(isset($args['fields']) && $args['fields'] === 'all')
		{
			foreach($organizers as $id => $organizer)
			{
				$organizers[$id]->organizer_meta = get_option('event_organizer_'.$organizer->term_id);
			}
		}

		return $organizers;
	}
	else
		return FALSE;
}


function em_get_organizer($term_id = NULL)
{
	if(taxonomy_exists('event-organizer'))
	{
		if($term_id === NULL)
		{
			$term = get_queried_object();

			if(is_tax() && is_object($term) && isset($term->term_id))
				$term_id = $term->term_id;
			else
				return NULL;
		}

		if(($organizer = get_term((int)$term_id, 'event-organizer', 'OBJECT', 'raw')) !== NULL)
		{
			$organizer->organizer_meta = get_option('event_organizer_'.$organizer->term_id);

			return $organizer;
		}
		else
			return NULL;
	}
	else
		return FALSE;
}


function em_get_organizers_for($post_id = 0)
{
	if(taxonomy_exists('event-organizer'))
	{
		if(is_array($organizers = wp_get_post_terms((int)$post_id, 'event-organizer')))
		{
			if(!empty($organizers))
			{
				foreach($organizers as $id => $organizer)
				{
					$organizers[$id]->organizer_meta = get_option('event_organizer_'.$organizer->term_id);
				}

				return $organizers;
			}
			else
				return array();
		}
		else
			return NULL;
	}
	else
		return FALSE;
}


function em_get_categories($args = array())
{
	if(taxonomy_exists('event-category'))
		return get_terms('event-category', $args);
	else
		return FALSE;
}


function em_get_category($term_id = NULL)
{
	if(!taxonomy_exists('event-category'))
		return FALSE;

	if($term_id === NULL)
	{
		$term = get_queried_object();

		if(is_tax() && is_object($term) && isset($term->term_id))
			$term_id = $term->term_id;
		else
			return NULL;
	}

	return (($category = get_term((int)$term_id, 'event-category', 'OBJECT', 'raw')) !== NULL ? $category : NULL);
}


function em_get_categories_for($post_id = 0)
{
	if(!taxonomy_exists('event-category'))
		return FALSE;

	if(is_array($categories = wp_get_post_terms((int)$post_id, 'event-category')))
		return (!empty($categories) ? $categories : array());
	else
		return NULL;
}


function em_get_the_start($post_id = 0, $type = 'datetime')
{
	$date = get_post_meta((int)$post_id, '_event_start_date', TRUE);

	if(empty($date))
		return FALSE;
	else
		return apply_filters('em_get_the_start', em_format_date($date, $type));
}


function em_get_the_end($post_id = 0, $type = 'datetime')
{
	$date = get_post_meta((int)$post_id, '_event_end_date', TRUE);

	if(empty($date))
		return FALSE;
	else
		return apply_filters('em_get_the_end', em_format_date($date, $type));
}


function em_format_date($date = NULL, $type = 'datetime', $format = FALSE)
{
	if($date === NULL)
		$date = current_time('timestamp', FALSE);

	$options = get_option('events_maker_general');
	$date_format = $options['datetime_format']['date'];
	$time_format = $options['datetime_format']['time'];

	if(is_array($format))
	{
		$date_format = (!empty($format['date']) ? $format['date'] : $date_format);
		$time_format = (!empty($format['time']) ? $format['time'] : $time_format);
	}

	if($type === 'date')
		return date_i18n($date_format, strtotime($date));
	elseif($type === 'time')
		return date($time_format, strtotime($date));
	else
		return date_i18n($date_format.' '.$time_format, strtotime($date));
}


function em_is_event_archive($datetype = '')
{
	global $wp_query;

	if(!is_post_type_archive('event'))
		return FALSE;

	if($datetype === '')
		return TRUE;

	if(!empty($wp_query->query_vars['event_ondate']))
	{
		$date = explode('/', $wp_query->query_vars['event_ondate']);

		if((($a = count($date)) === 1 && $datetype === 'year') || ($a === 2 && $datetype === 'month') || ($a === 3 && $datetype === 'day'))
			return TRUE;
	}

	return FALSE;
}


function em_get_event_date_link($year = 0, $month = 0, $day = 0)
{
	global $wp_rewrite;

	$archive = get_post_type_archive_link('event');

	$year = (int)$year;
	$month = (int)$month;
	$day = (int)$day;

	if($year === 0 && $month === 0 && $day === 0)
		return $archive;

	$em_year = $year;
	$em_month = str_pad($month, 2, '0', STR_PAD_LEFT);
	$em_day = str_pad($day, 2, '0', STR_PAD_LEFT);

	if($day !== 0)
		$link_date = compact('em_year', 'em_month', 'em_day');
	elseif($month !== 0)
		$link_date = compact('em_year', 'em_month');
	else
		$link_date = compact('em_year');

	if(!empty($archive) && $wp_rewrite->using_mod_rewrite_permalinks() && ($permastruct = $wp_rewrite->get_extra_permastruct('event_ondate')))
	{
		$archive = apply_filters('post_type_archive_link', home_url(str_replace('%event_ondate%', implode('/', $link_date), $permastruct)), 'event');
	}
	else
		$archive = add_query_arg('event_ondate', implode('-', $link_date), $archive);

	return $archive;
}


function em_display_event_categories($args = array())
{
	if(!taxonomy_exists('event-category'))
		return FALSE;

	return em_get_event_taxonomy('event-category', $args);
}


function em_display_event_locations($args = array())
{
	if(!taxonomy_exists('event-location'))
		return FALSE;

	return em_get_event_taxonomy('event-location', $args);
}


function em_display_event_organizers($args = array())
{
	if(!taxonomy_exists('event-organizer'))
		return FALSE;

	return em_get_event_taxonomy('event-organizer', $args);
}


function em_get_event_taxonomy($taxonomy = '', $args = array())
{
	$defaults = array(
		'display_as_dropdown' => FALSE,
		'show_hierarchy' => TRUE,
		'order_by' => 'name',
		'order' => 'desc'
	);

	$args = array_merge($defaults, $args);

	if($args['display_as_dropdown'] === FALSE)
	{
		return wp_list_categories(
			array(
				'orderby' => $args['order_by'],
				'order' => $args['order'],
				'hide_empty' => FALSE,
				'hierarchical' => (bool)$args['show_hierarchy'],
				'taxonomy' => $taxonomy,
				'echo' => FALSE,
				'style' => 'list',
				'title_li' => ''
			)
		);
	}
	else
	{
		return wp_dropdown_categories(
			array(
				'orderby' => $args['order_by'],
				'order' => $args['order'],
				'hide_empty' => FALSE, 
				'hierarchical' => (bool)$args['show_hierarchy'],
				'taxonomy' => $taxonomy,
				'hide_if_empty' => FALSE,
				'echo' => FALSE
			)
		);
	}
}


function em_display_event_archives($args = array())
{
	global $wp_locale;

	$defaults = array(
		'display_as_dropdown' => FALSE,
		'show_post_count' => TRUE,
		'type' => 'monthly',
		'order' => 'desc',
		'limit' => 0
	);

	$archives = $counts = array();
	$args = array_merge($defaults, $args);
	$cut = ($args['type'] === 'yearly' ? 4 : 7);

	$events = get_posts(
		array(
			'post_type' => 'event',
			'suppress_filters' => FALSE,
			'posts_per_page' => -1
		)
	);

	foreach($events as $event)
	{
		$startdatetime = get_post_meta($event->ID, '_event_start_date', TRUE);
		$enddatetime = get_post_meta($event->ID, '_event_end_date', TRUE);

		if(!empty($startdatetime))
		{
			$start_ym = substr($startdatetime, 0, $cut);
			$archives[] = $start_ym;

			if(isset($counts[$start_ym]))
				$counts[$start_ym]++;
			else
				$counts[$start_ym] = 1;
		}

		if(!empty($enddatetime))
		{
			$end_ym = substr($enddatetime, 0, $cut);
			$archives[] = $end_ym;

			if($start_ym !== $end_ym)
			{
				if(isset($counts[$end_ym]))
					$counts[$end_ym]++;
				else
					$counts[$end_ym] = 1;
			}
		}
	}

	$archives = array_unique($archives, SORT_STRING);
	natsort($archives);

	$elem_m = ($args['display_as_dropdown'] === TRUE ? 'select' : 'ul');
	$elem_i = ($args['display_as_dropdown'] === TRUE ? '<option value="%s">%s%s</option>' : '<li><a href="%s">%s</a>%s</li>');
	$html = sprintf('<%s>', $elem_m);

	foreach(array_slice(($args['order'] === 'desc' ? array_reverse($archives) : $archives), 0, ($args['limit'] === 0 ? NULL : $args['limit'])) as $archive)
	{
		if($args['type'] === 'yearly')
		{
			$link = em_get_event_date_link($archive);
			$display = $archive;
		}
		else
		{
			$date = explode('-', $archive);
			$link = em_get_event_date_link($date[0], $date[1]);
			$display = $wp_locale->get_month($date[1]).' '.$date[0];
		}

		$html .= sprintf(
			$elem_i,
			$link,
			$display,
			($args['show_post_count'] === TRUE ? ' ('.$counts[$archive].')' : '')
		);
	}

	$html .= sprintf('</%s>', $elem_m);

	return $html;
}


function em_display_events($args = array())
{
	$options = get_option('events_maker_general');
	$defaults = array(
		'number_of_events' => 5,
		'thumbnail_size' => 'thumbnail',
		'categories' => array(),
		'locations' => array(),
		'organizers' => array(),
		'order_by' => 'start',
		'order' => 'asc',
		'show_past_events' => $options['show_past_events'],
		'show_event_thumbnail' => TRUE,
		'show_event_excerpt' => FALSE,
		'no_events_message' => __('No Events', 'events-maker'),
		'date_format' => $options['datetime_format']['date'],
		'time_format' => $options['datetime_format']['time']
	);

	$args = apply_filters('em_display_events_args', array_merge($defaults, $args));

	$events_args = array(
		'post_type' => 'event',
		'suppress_filters' => FALSE,
		'posts_per_page' => ($args['number_of_events'] === 0 ? -1 : $args['number_of_events']),
		'order' => $args['order'],
		'event_show_past_events' => (bool)$args['show_past_events']
	);

	if(!empty($args['categories']))
	{
		$events_args['tax_query'][] = array(
			'taxonomy' => 'event-category',
			'field' => 'id',
			'terms' => $args['categories'],
			'include_children' => FALSE,
			'operator' => 'IN'
		);
	}

	if(!empty($args['locations']))
	{
		$events_args['tax_query'][] = array(
			'taxonomy' => 'event-location',
			'field' => 'id',
			'terms' => $args['locations'],
			'include_children' => FALSE,
			'operator' => 'IN'
		);
	}

	if(!empty($args['organizers']))
	{
		$events_args['tax_query'][] = array(
			'taxonomy' => 'event-organizer',
			'field' => 'id',
			'terms' => $args['organizers'],
			'include_children' => FALSE,
			'operator' => 'IN'
		);
	}

	if($args['order_by'] === 'start' || $args['order_by'] === 'end')
	{
		$events_args['orderby'] = 'meta_value';
		$events_args['meta_key'] = '_event_'.$args['order_by'].'_date';
	}
	elseif($args['order_by'] === 'publish')
		$events_args['orderby'] = 'date';
	else
		$events_args['orderby'] = 'title';

	$events = new WP_Query($events_args);

	if($events->have_posts())
	{
		$html = '
		<ul>';

		while($events->have_posts())
		{
			$events->the_post();
			$all_day_event = get_post_meta($events->post->ID, '_event_all_day', TRUE);
			$start_date = get_post_meta($events->post->ID, '_event_start_date', TRUE);
			$end_date = get_post_meta($events->post->ID, '_event_end_date', TRUE);
			$format = array('date' => $args['date_format'], 'time' => $args['time_format']);
			$format_c = array('date' => 'Y-m-d', 'time' => '');
			$same_dates = (bool)(em_format_date($start_date, 'date', $format_c) === em_format_date($end_date, 'date', $format_c));
			$end_date_html = ' -
				<span class="event-end-date post-date">
					<abbr class="dtend" title="%s">%s</abbr>
				</span>';

			$html .= '
			<li>
				<span class="event-start-date post-date">
					<abbr class="dtstart" title="'.$start_date.'">'.em_format_date($start_date, ($all_day_event === '0' ? 'datetime' : 'date'), $format).'</abbr>
				</span>';

			if($all_day_event === '1' && $same_dates === FALSE)
				$html .= sprintf($end_date_html, $end_date, em_format_date($end_date, 'date', $format));
			elseif($all_day_event === '0')
				$html .= sprintf($end_date_html, $end_date, em_format_date($end_date, ($same_dates === TRUE ? 'time' : 'datetime'), $format));

			$html .= '
				<br />';

			if($args['show_event_thumbnail'] === TRUE && has_post_thumbnail($events->post->ID))
			{
				$html .= '
				<span class="event-thumbnail">
					'.get_the_post_thumbnail($events->post->ID, $args['thumbnail_size']).'
				</span>';
			}

			$html .= '
				<a class="event-title" href="'.get_permalink($events->post->ID).'">'.$events->post->post_title.'</a>
				<br />';

			if($args['show_event_excerpt'] === TRUE)
				$html .= '
				<span class="event-excerpt">
					'.get_the_excerpt().'
				</span>';

			$html .= '
			</li>';
		}

		$html .= '
		</ul>';

		return $html;
	}
	else
		return $args['no_events_message'];
}


function em_display_google_map($args = array(), $locations = 0)
{
	$defaults = array(
		'width' => '100%',
		'height' => '200px',
		'zoom' => 15,
		'maptype' => 'roadmap',
		'locations' => ''
	);

	$defaults_bool = array(
		'maptypecontrol' => TRUE,
		'zoomcontrol' => TRUE,
		'streetviewcontrol' => TRUE,
		'overviewmapcontrol' => FALSE,
		'pancontrol' => FALSE,
		'rotatecontrol' => FALSE,
		'scalecontrol' => FALSE,
		'draggable' => TRUE,
		'keyboardshortcuts' => TRUE,
		'scrollzoom' => TRUE
	);

	$defaults = array_merge($defaults, $defaults_bool, $args);

	$tmp = array();

	foreach($args as $arg => $value)
	{
		if(in_array($arg, array_keys($defaults_bool), TRUE))
		{
			$tmp[$arg] = ($value === TRUE ? 'on' : 'off');
		}
	}

	extract(array_merge($defaults, $tmp), EXTR_PREFIX_ALL, 'em');

	if(is_array($locations) && !empty($locations))
	{
		$locations_tmp = array();

		foreach($locations as $location)
		{
			$locations_tmp[] = (int)$location;
		}

		$locations_tmp = array_unique($locations_tmp);
		$em_locations = implode(',', $locations_tmp);
	}
	elseif(is_numeric($locations))
		$em_locations = ((int)$locations !== 0 ? (int)$locations : '');

	do_shortcode('[em-google-map locations="'.$em_locations.'" width="'.$em_width.'" height="'.$em_height.'" zoom="'.$em_zoom.'" maptype="'.$em_maptype.'" maptypecontrol="'.$em_maptypecontrol.'" zoomcontrol="'.$em_zoomcontrol.'" streetviewcontrol="'.$em_streetviewcontrol.'" overviewmapcontrol="'.$em_overviewmapcontrol.'" pancontrol="'.$em_pancontrol.'" rotatecontrol="'.$em_rotatecontrol.'" scalecontrol="'.$em_scalecontrol.'" draggable="'.$em_draggable.'" keyboardshortcuts="'.$em_keyboardshortcuts.'" scrollzoom="'.$em_scrollzoom.'"]');
}
?>