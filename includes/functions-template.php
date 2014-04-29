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

// Display events list
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
			'show_event_thumbnail' => true,
			'show_event_excerpt' => false,
			'no_events_message' => __('No Events', 'events-maker'),
			'date_format' => $options['datetime_format']['date'],
			'time_format' => $options['datetime_format']['time']
		);
	
		$args = apply_filters('em_display_events_args', array_merge($defaults, $args));
	
		$events_args = array(
			'post_type' => 'event',
			'suppress_filters' => false,
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
	
		$events = new WP_Query($events_args);
	
		if($events->have_posts())
		{
			$html = '
			<ul>';
	
			while($events->have_posts())
			{
				$events->the_post();
				$all_day_event = get_post_meta($events->post->ID, '_event_all_day', true);
				$start_date = get_post_meta($events->post->ID, '_event_start_date', true);
				$end_date = get_post_meta($events->post->ID, '_event_end_date', true);
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
	
				if($all_day_event === '1' && $same_dates === false)
					$html .= sprintf($end_date_html, $end_date, em_format_date($end_date, 'date', $format));
				elseif($all_day_event === '0')
					$html .= sprintf($end_date_html, $end_date, em_format_date($end_date, ($same_dates === true ? 'time' : 'datetime'), $format));
	
				$html .= '
					<br />';
	
				if($args['show_event_thumbnail'] === true && has_post_thumbnail($events->post->ID))
				{
					$html .= '
					<span class="event-thumbnail">
						'.get_the_post_thumbnail($events->post->ID, $args['thumbnail_size']).'
					</span>';
				}
	
				$html .= '
					<a class="event-title" href="'.get_permalink($events->post->ID).'">'.$events->post->post_title.'</a>
					<br />';
	
				if($args['show_event_excerpt'] === true)
					$html .= '
					<span class="event-excerpt">
						'.get_the_excerpt().'
					</span>';
	
				$html .= '
				</li>';
			}
	
			$html .= '
			</ul>';
	
			return apply_filters('em_display_events', $html);
		}
		else
			return $args['no_events_message'];
	}
}


// Display event categories
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


// Display event tags
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
			<footer class="entry-meta">
				
				<span class="term-list event-tag tag-links"><?php echo $tags; ?></span>
				
			</footer>
		<?php
		}
	}
}


// Display event locations
if (!function_exists('em_display_event_locations'))
{
	function em_display_event_locations($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		?>
		<?php $taxonomy = 'event-location'; ?>
        <?php $terms = em_get_locations_for($post_id); ?>
        <?php if ($terms) : ?>
        	
        <div class="entry-meta">
        	
        	<span class="term-list event-location cat-links"><strong><?php _e('Location', 'events-maker'); ?>: </strong>
        	<?php foreach ($terms as $term) : ?>
            	<?php $term_link = get_term_link($term->slug, $taxonomy); ?>
                <?php if(is_wp_error($term_link)) continue; ?>
				<a href="<?php echo $term_link; ?>" class="location"><?php echo $term->name; ?></a>
				<?php // Location details
				if ($event_display_options['display_location_details'] === 1) : ?>
					<?php $location_details = $term->location_meta; ?>
					<?php if ($location_details) : ?>
						<?php echo !empty($location_details['address']) ? $location_details['address'] : ''; ?>
						<?php echo !empty($location_details['zip']) ? $location_details['zip'] : ''; ?>
						<?php echo !empty($location_details['city']) ? $location_details['city'] : ''; ?>
						<?php echo !empty($location_details['state']) ? $location_details['state'] : ''; ?>
						<?php echo !empty($location_details['country']) ? $location_details['country'] : ''; ?>
					<?php endif; ?>
				<?php endif; ?>
            <?php endforeach; ?>
            </span>
            
        </div>
        
        <?php endif; // if event locations ?>
    <?php
	}
}


// Display event organizers
if (!function_exists('em_display_event_organizers'))
{
	function em_display_event_organizers($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		?>
        <?php $taxonomy = 'event-organizer'; ?>
        <?php $terms = em_get_organizers_for($post_id); ?>
        <?php if ($terms) : ?>
        	
        <div class="entry-meta">
        	
        	<span class="term-list event-organizer cat-links"><strong><?php _e('Organizer', 'events-maker'); ?>: </strong>
        	<?php foreach ($terms as $term) : ?>
            	<?php $term_link = get_term_link($term->slug, $taxonomy); ?>
                <?php if(is_wp_error($term_link)) continue; ?>
            	<a href="<?php echo $term_link; ?>" class="org"><?php echo $term->name; ?></a>
            	<?php // Organizer details
            	if ($event_display_options['display_organizer_details'] === 1) : ?>
            		<?php $organizer_details = $term->organizer_meta; ?>
            		<?php if ($organizer_details) : ?>
                		<?php echo !empty($organizer_details['contact_name']) ? '<span class="fn">'.$organizer_details['contact_name'].'</span>' : ''; ?>
                		<?php echo !empty($organizer_details['phone']) ? '<span class="tel">'.$organizer_details['phone'].'</span>' : ''; ?>
                		<?php echo !empty($organizer_details['email']) ? '<span class="email">'.$organizer_details['email'].'</span>' : ''; ?>
                		<?php echo !empty($organizer_details['website']) ? '<span class="url">'.$organizer_details['website'].'</span>' : ''; ?>
            		<?php endif; ?>
            	<?php endif; ?>
            <?php endforeach; ?>
            </span>
            
        </div>
        
        <?php endif; // if event organizers ?>
    <?php
	}
}


// Display event tickets
if (!function_exists('em_display_event_tickets'))
{
	function em_display_event_tickets($post_id = 0)
	{
		$post_id = (int)(empty($post_id) ? get_the_ID() : $post_id);
		
		if(empty($post_id))
			return false;
		?>
		<div class="entry-meta">
			
           	<?php // Tickets list 
           	$tickets = em_get_tickets($post_id);?>
           	<?php if ($tickets) : ?>
           		<div class="event-tickets tickets">
           			<span class="tickets-label"><?php echo __('Tickets', 'events-maker'); ?>: </span>
               		<?php foreach ($tickets as $ticket) : ?>
               			<?php echo '<span class="event-ticket">'; ?>
							<?php echo '<span class="ticket-name">' . esc_html($ticket['name']) . ': </span>'; ?>
							<?php echo '<span class="ticket-price">' . esc_html(em_get_currency_symbol($ticket['price'])) . '</span>'; ?>
						<?php echo '</span>'; ?>
               		<?php endforeach; ?>
           		</div>
           	<?php else : ?>
           		<div class="event-tickets tickets">
           			<span class="tickets-label"><?php echo __('Tickets', 'events-maker'); ?>: </span>
           			<?php echo '<span class="event-ticket">'; ?>
						<?php echo '<span class="ticket-name">' . __('Free', 'events-maker') . ': </span>'; ?>
						<?php echo '<span class="ticket-price">' . em_get_currency_symbol($ticket['price']) . '</span>'; ?>
					<?php echo '</span>'; ?>
       			</div>
       		<?php endif; ?>
       		
       		<?php // Tickets URL
       		$tickets_url = get_post_meta($post_id, '_event_tickets_url', true); ?>
       		<?php if ($tickets_url) : ?>
       			<div class="event-tickets-url tickets">
       				<span class="tickets-url-label"><?php _e('Buy tickets URL', 'events-maker'); ?>: </span>
       				<a href="<?php echo esc_url($tickets_url); ?>" class="tickets-url-link" rel="nofollow" target="_blank"><?php echo esc_url($tickets_url); ?></a>
       			</div>
       		<?php endif; ?>
       		
   		</div>
	<?php
	}
}

// Display event date
if (!function_exists('em_display_event_date'))
{
	function em_display_event_date($format = '')
	{
		global $post;
		
		$date 			= em_get_the_date($post->ID, array('format' => array('date' => 'Y-m-d', 'time' => 'G:i')));
		$all_day_event 	= em_is_all_day($post->ID);
		$separator		= ' - ';
		
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
				$date_output = implode(' '.$separator.' ', $date); 
			}
		}
		// is not all day, one day, different hours
		elseif(!$all_day_event && !empty($date['start']) && !empty($date['end']))
		{
			// one day only
			if(em_format_date($date['start'], 'date') === em_format_date($date['end'], 'date'))
			{
				$date_output = em_format_date($date['start'], 'datetime', $format);
			}
			// more than one day
			else
			{
				$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $separator . ' ' . em_format_date($date['end'], 'datetime', $format); 
			}
		}
		// any other case
		else 
		{		
			$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $separator . ' ' . em_format_date($date['end'], 'datetime', $format);  
		}
		
		printf('<span class="entry-date date"><a href="%1$s" rel="bookmark"><abbr class="dtstart" title="%2$s"></abbr><abbr class="dtend" title="%3$s"></abbr>%4$s</a></span> <span class="byline"><span class="author vcard"><a class="url fn n" href="%5$s" rel="author">%6$s</a></span></span>',
			esc_url(get_permalink()),
			esc_attr($date['start']),
			esc_attr($date['end']),
			esc_html($date_output),
			esc_url(get_author_posts_url(get_the_author_meta('ID'))),
			get_the_author()
		);
	}
}


// Display event occurrences date
if (!function_exists('em_display_event_occurrences'))
{
	function em_display_event_occurrences($format = '')
	{
		$occurrences 	= em_get_occurrences();
		$all_day_event 	= em_is_all_day();
		$separator		= ' - ';
		
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
						$date_output = implode(' '.$separator.' ', $date); 
					}
				}
				// is not all day, one day, different hours
				elseif(!$all_day_event && !empty($date['start']) && !empty($date['end']))
				{
					// one day only
					if(em_format_date($date['start'], 'date') === em_format_date($date['end'], 'date'))
					{
						$date_output = em_format_date($date['start'], 'datetime', $format);
					}
					// more than one day
					else
					{
						$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $separator . ' ' . em_format_date($date['end'], 'datetime', $format); 
					}
				}
				// any other case
				else 
				{		
					$date_output = em_format_date($date['start'], 'datetime', $format) . ' ' . $separator . ' ' . em_format_date($date['end'], 'datetime', $format);  
				}
				
				printf('<span class="entry-date date"><a href="%1$s" rel="bookmark"><abbr class="dtstart" title="%2$s"></abbr><abbr class="dtend" title="%3$s"></abbr>%4$s</a></span> <span class="byline"><span class="author vcard"><a class="url fn n" href="%5$s" rel="author">%6$s</a></span></span>',
					esc_url(get_permalink()),
					esc_attr($date['start']),
					esc_attr($date['end']),
					esc_html($date_output)
				);
			}
			printf('<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
				esc_url(get_author_posts_url(get_the_author_meta('ID'))),
				get_the_author()
			);
		}
	}
}


// Display pagination links
if (!function_exists('em_paginate_links'))
{
	function em_paginate_links($args = array())
	{
		global $wp_query;
	
		$big = 999999999; // need an unlikely integer
	
		$defaults = array(
			'base' 			=> str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
			'format' 		=> '?paged=%#%',
			'total'			=> $wp_query->max_num_pages,
			'current'		=> max(1, get_query_var('paged')),
			'show_all'		=> false,
			'end_size'		=> 1,
			'mid_size'		=> 2,
			'prev_next'		=> true,
			'prev_text'		=> __('&laquo; Previous', 'events-maker'),
			'next_text'		=> __('Next &raquo;', 'events-maker'),
			'type'			=> 'plain',
			'add_args'		=> False,
			'add_fragment'	=> ''
		);
	
		$args = apply_filters('em_paginate_links_args', array_merge($defaults, $args));
	
		echo paginate_links($args);
	}
}
?>