<?php
/**
 * Events Maker pluggable template functions
 *
 * Override any of those functions by copying it to your theme or replace it via plugin
 *
 * @author 	Digital Factory
 * @package Events Maker/Functions
 * @version 1.1.0
 */
 
if(!defined('ABSPATH')) exit;

/**
 * Display events list
 */
if (!function_exists('em_display_events'))
{
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
			'show_occurrences' => $options['show_occurrences'],
			'show_event_thumbnail' => true,
			'show_event_excerpt' => false,
			'no_events_message' => __('Apologies, but no events were found.', 'events-maker'),
			'date_format' => $options['datetime_format']['date'],
			'time_format' => $options['datetime_format']['time']
		);
	
		$args = apply_filters('em_display_events_args', array_merge($defaults, $args));

		$events_args = array(
			'post_type' => 'event',
			'suppress_filters' => false,
			'posts_per_page' => ($args['number_of_events'] === 0 ? -1 : $args['number_of_events']),
			'order' => $args['order'],
			'event_show_past_events' => (bool)$args['show_past_events'],
			'event_show_occurrences' => (bool)$args['show_occurrences'],
			'event_show_featured' => (bool)$args['show_featured']
		);
	
		if(!empty($args['categories']))
		{
			$events_args['tax_query'][] = array(
				'taxonomy' => 'event-category',
				'field' => 'id',
				'terms' => $args['categories'],
				'include_children' => false,
				'operator' => 'IN'
			);
		}
	
		if(!empty($args['locations']))
		{
			$events_args['tax_query'][] = array(
				'taxonomy' => 'event-location',
				'field' => 'id',
				'terms' => $args['locations'],
				'include_children' => false,
				'operator' => 'IN'
			);
		}
	
		if(!empty($args['organizers']))
		{
			$events_args['tax_query'][] = array(
				'taxonomy' => 'event-organizer',
				'field' => 'id',
				'terms' => $args['organizers'],
				'include_children' => false,
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
			
		$events = get_posts($events_args);
		
		if ($events)
		{
			ob_start();
			
			echo apply_filters('em_display_events_wrapper_start', '<ul class="events-list">');
			
			foreach ($events as $post)
			{
				setup_postdata($post);
				
				em_get_template('content-widget-event.php', array($post, $args));
			}
			
			wp_reset_postdata();
			
			echo apply_filters('em_display_events_wrapper_end', '</ul>');
			
			$html = ob_get_contents();
			ob_end_clean();
			
			return apply_filters('em_display_events', $html);
		}
		else
			return $args['no_events_message'];
	}
}


/**
 * Display event categories
 */
if (!function_exists('em_display_event_categories'))
{
	function em_display_event_categories($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		
		$categories = get_the_term_list($post_id, 'event-category', __('<strong>Category: </strong>', 'events-maker'), ', ', '');
		if ($categories && !is_wp_error($categories)) 
		{ ?>
			<div class="entry-meta">
				
				<span class="term-list event-category cat-links"><?php echo $categories; ?></span>
				
			</div>
		<?php
		}
	}
}


/**
 * Display event tags
 */
if (!function_exists('em_display_event_tags'))
{
	function em_display_event_tags($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		
		$tags = get_the_term_list($post_id, 'event-tag', __('<strong>Tags: </strong>', 'events-maker'), '', '');
		if ($tags && !is_wp_error($tags)) 
		{ ?>
			<footer class="entry-footer">
				
				<div class="entry-meta">
					
					<span class="term-list event-tag tags-links tag-links"><?php echo $tags; ?></span>
					
				</div>
				
			</footer>
		<?php
		}
	}
}


/**
 * Display event locations
 */
if (!function_exists('em_display_event_locations'))
{
	function em_display_event_locations($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		
		$locations = em_get_locations_for($post_id);
		
		if(empty($locations) || is_wp_error($locations))
			return false;
		?>
		
		<?php $output = get_the_term_list($post_id, 'event-location', __('<strong>Location: </strong>', 'events-maker'), ', ', ''); ?>
		
		<div class="entry-meta">
			
			<span class="term-list event-location cat-links">

			<?php if (is_single()) : ?>
				
				<?php $event_display_options = get_post_meta($post_id, '_event_display_options', TRUE); // event display options ?>
				
				<?php if (!empty($event_display_options) && $event_display_options['display_location_details'] === 1) : ?>
					
					<?php $output = __('<strong>Location: </strong>', 'events-maker');
		        	
			        	foreach ($locations as $term) :
							
							$output .= '<span class="single-location term-' . $term->term_id . '">';
			        		
			            	$term_link = get_term_link($term->slug, 'event-location');
			                
			                if (is_wp_error($term_link))
			                	continue;
							
							$output .= '<a href="' . $term_link . '" class="location">' . $term->name . '</a>';
							
							// Location details
							$location_details = $term->location_meta;
							if ($location_details) :
								$output .= ' ';
								$output .= !empty($location_details['address']) ? $location_details['address'] . ' ' : '';
								$output .= !empty($location_details['zip']) ? $location_details['zip'] . ' ' : '';
								$output .= !empty($location_details['city']) ? $location_details['city'] . ' ' : '';
								$output .= !empty($location_details['state']) ? $location_details['state'] . ' ' : '';
								$output .= !empty($location_details['country']) ? $location_details['country'] . ' ' : '';
								$output .= ' ';
							endif;
							
							$output .= '</span>';
							
			            endforeach; ?>
					
				<?php endif; // display location details ?>

			<?php endif; // single ?>
			
			<?php echo $output; ?>
		
			</span>
			
		</div>

    <?php
	}
}


/**
 * Display event organizers
 */
if (!function_exists('em_display_event_organizers'))
{
	function em_display_event_organizers($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		
		$organizers = em_get_organizers_for($post_id);
		
		if(empty($organizers) || is_wp_error($organizers))
			return false;
		?>
		
		<?php $output = get_the_term_list($post_id, 'event-organizer', __('<strong>Organizer: </strong>', 'events-maker'), ', ', ''); ?>
		
		<div class="entry-meta">
			
			<span class="term-list event-organizer cat-links">

			<?php if (is_single()) : ?>
				
				<?php $event_display_options = get_post_meta($post_id, '_event_display_options', TRUE); // event display options ?>
				
				<?php if (!empty($event_display_options) && $event_display_options['display_organizer_details'] === 1) : ?>
					
					<?php $output = __('<strong>Organizer: </strong>', 'events-maker');
		        	
			        	foreach ($organizers as $term) :
							
							$output .= '<span class="single-organizer term-' . $term->term_id . '">';
			        		
			            	$term_link = get_term_link($term->slug, 'event-organizer');
			                
			                if (is_wp_error($term_link))
			                	continue;
							
							$output .= '<a href="' . $term_link . '" class="organizer">' . $term->name . '</a>';
							
							// Location details
							$organizer_details = $term->organizer_meta;
							if ($organizer_details) :
								$output .= ' ';
								$output .= !empty($organizer_details['contact_name']) ? $organizer_details['contact_name'] . ' ' : '';
								$output .= !empty($organizer_details['phone']) ? $organizer_details['phone'] . ' ' : '';
								$output .= !empty($organizer_details['email']) ? $organizer_details['email'] . ' ' : '';
								$output .= !empty($organizer_details['website']) ? $organizer_details['website'] . ' ' : '';
								$output .= ' ';
							endif;
							
							$output .= '</span>';
							
			            endforeach; ?>
					
				<?php endif; // display location details ?>

			<?php endif; // single ?>
			
			<?php echo $output; ?>
		
			</span>
			
		<div>

    <?php
	}
}


/**
 * Display event tickets
 */
if (!function_exists('em_display_event_tickets'))
{
	function em_display_event_tickets($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		
		em_get_template('single-event/tickets.php');

	}
}


/**
 * Display event date
 */
if (!function_exists('em_display_event_date'))
{
	function em_display_event_date($format = '', $args = array())
	{
		global $post;
		
		$date 			= em_get_the_date($post->ID, array('format' => array('date' => 'Y-m-d', 'time' => 'G:i')));
		$all_day_event 	= em_is_all_day($post->ID);
		$html			= '';
		
		// default args
		$defaults = array(
			'separator' => ' - ',
			'format' => 'link',
			'before' => '',
			'after' => '',
			'show_author_link' => false,
			'echo' => true
		);
		$args = apply_filters('em_display_event_date_args', wp_parse_args($args, $defaults));
		
		// date format options
		$options = get_option('events_maker_general');
		$date_format = $options['datetime_format']['date'];
		$time_format = $options['datetime_format']['time'];
		
		// if format was set, use it
		if(!empty($format) && is_array($format))
		{
			$date_format = (!empty($format['date']) ? $format['date'] : $date_format);
			$time_format = (!empty($format['time']) ? $format['time'] : $time_format);
		}
		
		// is all day
		if($all_day_event && !empty($date['start']) && !empty($date['end']))
		{
			// format date (date only)
			$date['start'] = em_format_date($date['start'], 'date', $date_format);
			$date['end'] = em_format_date($date['end'], 'date', $date_format);
	
			// one day only
			if($date['start'] === $date['end'])
			{
				$date_output = $date['start'];
			}
			// more than one day
			else
			{
				$date_output = implode(' '.$args['separator'].' ', $date); 
			}
		}
		// is not all day, one day, different hours
		elseif(!$all_day_event && !empty($date['start']) && !empty($date['end']))
		{
			// one day only
			if(em_format_date($date['start'], 'date') === em_format_date($date['end'], 'date'))
			{
				$date_output = em_format_date($date['start'], 'datetime', $format)  . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'time', $format); 
			}
			// more than one day
			else
			{
				$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'datetime', $format); 
			}
		}
		// any other case
		else 
		{		
			$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'datetime', $format);  
		}
		
		// generate output
		$html .= $args['before'];
		
		// output format
		if ($args['format'] == 'link')
			$html .= sprintf('<span class="entry-date date"><a href="%1$s" rel="bookmark"><abbr class="dtstart" title="%2$s"></abbr><abbr class="dtend" title="%3$s"></abbr>%4$s</a></span>',
				esc_url(get_permalink()),
				esc_attr($date['start']),
				esc_attr($date['end']),
				esc_html($date_output)
			);
		else
			$html .= sprintf('<span class="entry-date date"><abbr class="dtstart" title="%1$s"></abbr><abbr class="dtend" title="%2$s"></abbr>%3$s</span>',
				esc_attr($date['start']),
				esc_attr($date['end']),
				esc_html($date_output)
			);
		
		// author link
		if ($args['show_author_link'] === true)
		{
			$html .= sprintf('<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
				esc_url(get_author_posts_url(get_the_author_meta('ID'))),
				get_the_author()
			);
		}
		
		$html .= $args['after'];
		
		$html = apply_filters('em_display_event_date', $html);
		
		if ($args['echo'] === true)
			echo $html;
		else
			return $html;
		
	}
}


/**
 * Display event occurrences date
 */
if (!function_exists('em_display_event_occurrences'))
{
	function em_display_event_occurrences($format = '', $args = array())
	{
		$occurrences 	= em_get_occurrences();
		$all_day_event 	= em_is_all_day();
		$html			= '';
		
		// default args
		$defaults = array(
			'separator' => ' - ',
			'format' => 'link',
			'before' => '',
			'after' => '',
			'show_author_link' => false,
			'echo' => true
		);
		$args = apply_filters('em_display_event_occurrences_args', wp_parse_args($args, $defaults));
		
		// date format options
		$options = get_option('events_maker_general');
		$date_format = $options['datetime_format']['date'];
		$time_format = $options['datetime_format']['time'];
		
		// if format was set, use it
		if(!empty($format) && is_array($format))
		{
			$date_format = (!empty($format['date']) ? $format['date'] : $date_format);
			$time_format = (!empty($format['time']) ? $format['time'] : $time_format);
		}
		
		// generate output
		$html .= $args['before'];
		
		if (!empty($occurrences))
		{
			foreach ($occurrences as $date)
			{
				// is all day
				if($all_day_event && !empty($date['start']) && !empty($date['end']))
				{
					// format date (date only)
					$date['start'] = em_format_date($date['start'], 'date', $date_format);
					$date['end'] = em_format_date($date['end'], 'date', $date_format);
			
					// one day only
					if($date['start'] === $date['end'])
					{
						$date_output = $date['start'];
					}
					// more than one day
					else
					{
						$date_output = implode(' '. $args['separator'] .' ', $date); 
					}
				}
				// is not all day, one day, different hours
				elseif(!$all_day_event && !empty($date['start']) && !empty($date['end']))
				{
					// one day only
					if(em_format_date($date['start'], 'date') === em_format_date($date['end'], 'date'))
					{
						$date_output = em_format_date($date['start'], 'datetime', $format)  . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'time', $format); 
					}
					// more than one day
					else
					{
						$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'datetime', $format); 
					}
				}
				// any other case
				else 
				{		
					$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $args['separator'] . ' ' . em_format_date($date['end'], 'datetime', $format);  
				}
				
				// output format
				if ($args['format'] == 'link')
					$html .= sprintf('<span class="entry-date date"><a href="%1$s" rel="bookmark"><abbr class="dtstart" title="%2$s"></abbr><abbr class="dtend" title="%3$s"></abbr>%4$s</a></span>',
						esc_url(get_permalink()),
						esc_attr($date['start']),
						esc_attr($date['end']),
						esc_html($date_output)
					);
				else
					$html .= sprintf('<span class="entry-date date"><abbr class="dtstart" title="%1$s"></abbr><abbr class="dtend" title="%2$s"></abbr>%3$s</span>',
						esc_attr($date['start']),
						esc_attr($date['end']),
						esc_html($date_output)
					);
			}
		}

		// author link
		if ($args['show_author_link'] === true)
		{
			$html .= sprintf('<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
				esc_url(get_author_posts_url(get_the_author_meta('ID'))),
				get_the_author()
			);
		}
		
		$html .= $args['after'];
		
		$html = apply_filters('em_display_event_occurrences', $html);
		
		if ($args['echo'] === true)
			echo $html;
		else
			return $html;
	}
}


/**
 * Display page title
 */
if (!function_exists('em_page_title'))
{
	function em_page_title($echo = true)
	{
		$date = get_query_var('event_ondate');

		if (em_is_event_archive('day'))
			$page_title = sprintf(__('Event Daily Archives: %s', 'events-maker'), '<span>' . em_format_date($date, 'date') . '</span>');
		elseif (em_is_event_archive('month'))
			$page_title = sprintf(__('Event Monthly Archives: %s', 'events-maker'), '<span>' . em_format_date($date.'/28', 'date', _x('F Y', 'monthly archives date format', 'events-maker')) . '</span>');
		elseif (em_is_event_archive('year'))
			$page_title = sprintf(__('Event Yearly Archives: %s', 'events-maker'), '<span>' . em_format_date($date.'/01/28', 'date', _x('Y', 'yearly archives date format', 'events-maker')) . '</span>');
		elseif (is_tax('event-category'))
			$page_title = sprintf(__('Events Category: %s', 'events-maker'), single_term_title('', false));
		elseif (is_tax('event-location'))
			$page_title = sprintf(__('Events Location: %s', 'events-maker'), single_term_title('', false));
		elseif (is_tax('event-organizer'))
			$page_title = sprintf(__('Events Organizer: %s', 'events-maker'), single_term_title('', false));
		elseif (is_tax('event-tag'))
			$page_title = sprintf(__('Events Tag: %s', 'events-maker'), single_term_title('', false));
		else
			$page_title = __('Events', 'events-maker');
		
		$page_title = apply_filters('em_page_title', $page_title);
		
		if ($echo)
			echo $page_title;
		else
			return $page_title;
	}
}


/**
 * Show an archive description on taxonomy archives
 */
if (!function_exists('em_taxonomy_archive_description'))
{
	function em_taxonomy_archive_description()
	{
		if (is_tax(array('event-category', 'event-location', 'event-organizer', 'event-tag')) && get_query_var('paged') == 0)
		{
			$term_description = term_description();
			
			if (!empty($term_description))
			{
				?>
				<div class="archive-description term-description taxonomy-description">
					
					<?php echo apply_filters('em_taxonomy_archive_description', $term_description); ?>
					
				</div>
				<?php
			}
		}
	}
}


/**
 * Display google map in archive
 */
if (!function_exists('em_display_loop_event_google_map'))
{
	function em_display_loop_event_google_map()
	{
		if (is_tax('event-location'))
			em_get_template('loop-event/google-map.php');
	}
}


/**
 * Display location details
 */
if (!function_exists('em_display_location_details'))
{
	function em_display_location_details()
	{
		if (is_tax('event-location'))
			em_get_template('loop-event/location-details.php');
	}
}


/**
 * Display organizer details
 */
if (!function_exists('em_display_organizer_details'))
{
	function em_display_organizer_details()
	{
		if (is_tax('event-organizer'))
			em_get_template('loop-event/organizer-details.php');
	}
}


/**
 * Display content wrapper start
 */
if (!function_exists('em_output_content_wrapper_start'))
{
	function em_output_content_wrapper_start()
	{
		em_get_template('global/wrapper-start.php');
	}
}


/**
 * Display content wrapper end
 */
if (!function_exists('em_output_content_wrapper_end'))
{
	function em_output_content_wrapper_end()
	{
		em_get_template('global/wrapper-end.php');
	}
}


/**
 * Display breadcrumbs
 */
if (!function_exists('em_breadcrumb'))
{
	function em_breadcrumb()
	{
		em_get_template('global/breadcrumb.php');
	}
}


/**
 * Display pagination links
 */
if (!function_exists('em_paginate_links'))
{
	function em_paginate_links()
	{
		em_get_template('loop-event/pagination.php');
	}
}


/**
 * Display breadcrumbs
 */
if (!function_exists('em_sorting'))
{
	function em_sorting()
	{
		// em_get_template('loop-event/sorting.php');
	}
}


/**
 * Display sidebar
 */
if (!function_exists('em_get_sidebar'))
{
	function em_get_sidebar()
	{
		em_get_template('global/sidebar.php');
	}
}


/**
 * Display event thumbnail in loop
 */
if (!function_exists('em_display_loop_event_thumbnail'))
{
	function em_display_loop_event_thumbnail()
	{
		em_get_template('loop-event/thumbnail.php');
	}
}


/**
 * Display event meta in loop
 */
if (!function_exists('em_display_loop_event_meta'))
{
	function em_display_loop_event_meta()
	{
		em_get_template('loop-event/meta.php');
	}
}


/**
 * Display event excerpt in loop
 */
if (!function_exists('em_display_event_excerpt'))
{
	function em_display_event_excerpt()
	{
		em_get_template('loop-event/excerpt.php');
	}
}


/**
 * Display single event thumbnail
 */
if (!function_exists('em_display_single_event_thumbnail'))
{
	function em_display_single_event_thumbnail()
	{
		em_get_template('single-event/thumbnail.php');
	}
}


/**
 * Display single event content
 */
if (!function_exists('em_display_event_content'))
{
	function em_display_event_content()
	{
		em_get_template('single-event/content.php');
	}
}


/**
 * Display single event meta
 */
if (!function_exists('em_display_single_event_meta'))
{
	function em_display_single_event_meta()
	{
		em_get_template('single-event/meta.php');
	}
}


/**
 * Display single event date
 */
if (!function_exists('em_display_single_event_date'))
{
	function em_display_single_event_date()
	{
		// is recurring?
		if (em_is_recurring())
		{
			// display occurrences date
			em_display_event_occurrences();
		}
		else
		{
			// display event date
			em_display_event_date();
		}
	}
}

	
/**
 * Display google map in event
 */
if (!function_exists('em_display_single_event_google_map'))
{
	function em_display_single_event_google_map()
	{
		em_get_template('single-event/google-map.php');
	}
}


/**
 * Display widget event date
 */
if (!function_exists('em_display_widget_event_date'))
{
	function em_display_widget_event_date()
	{ 
		// display event date
		em_display_event_date('', $args = array('format' => ''));
	}
}