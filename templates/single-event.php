<?php get_header(); ?>

	<div id="primary" class="site-content">
		
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	                <header class="entry-header">
	                	
	                    <h1 class="entry-title"><?php the_title(); ?></h1>

		        		<?php // Display Display Options
		        		$event_display_options = get_post_meta($post->ID, '_event_display_options', TRUE); ?>
		                
		                <?php // Display Google Map
		                echo $event_display_options['google_map'] === 1 ? em_display_google_map() : ''; ?>
		                
		                <div class="entry-meta">
		                
			                <?php // Display Event Start ?>
			                <?php $event_start = em_get_the_start($post->ID) ? em_get_the_start($post->ID) : ''; ?>
			                <?php if ($event_start) : ?>
			                	<?php $event_start = em_is_all_day($post->ID) === TRUE ? em_get_the_start($post->ID, 'date') : em_get_the_start($post->ID); ?>
			                	<div class="event-start-date"><strong><?php _e('Start', 'events-maker'); ?>: </strong><abbr class="dtstart" title="<?php echo get_post_meta((int)$post->ID, '_event_start_date', TRUE); ?>"><?php echo $event_start; ?></abbr></div>
			                <?php endif; ?>
			                
			                <?php // Display Event End ?>
			                <?php $event_end = em_get_the_end($post->ID) ? em_get_the_end($post->ID) : ''; ?>
			                <?php if ($event_end) : ?>
			                	<?php $event_end = em_is_all_day($post->ID) === TRUE ? em_get_the_end($post->ID, 'date') : em_get_the_end($post->ID); ?>
			                	<div class="event-end-date"><strong><?php _e('End', 'events-maker'); ?>: </strong><abbr class="dtend" title="<?php echo get_post_meta((int)$post->ID, '_event_end_date', TRUE); ?>"><?php echo $event_end; ?></abbr></div>
			               	<?php endif; ?>
			               	
			               	<?php // Display Tickets details
			                if ($event_display_options['price_tickets_info'] === 1) : ?>
				               	<?php // Tickets URL 
				               	$tickets = em_get_tickets($post->ID);?>
				               	<?php if ($tickets) : ?>
				               		<div class="event-tickets"><strong><?php _e('Tickets', 'events-maker'); ?>: </strong>
					               		<?php foreach ($tickets as $ticket) : ?>
											<span><?php echo $ticket['name'] . ': '.em_get_currency_symbol($ticket['price']); ?></span>
					               		<?php endforeach; ?>
				               		</div>
				               	<?php else : ?>
				               		<div class="event-tickets"><strong><?php _e('Tickets', 'events-maker'); ?>: </strong>
			               				<?php _e('Free', 'events-maker'); ?>
			               			</div>
			               		<?php endif; ?>
			               		<?php // Tickets URL
			               		$tickets_url = get_post_meta($post->ID, '_event_tickets_url', TRUE); ?>
			               		<?php if ($tickets_url) : ?>
			               			<div class="event-tickets"><strong><?php _e('Buy tickets URL', 'events-maker'); ?>: </strong>
			               				<a href="<?php echo esc_url($tickets_url); ?>" rel="nofollow" target="_blank"><?php echo $tickets_url; ?></a>
			               			</div>
			               		<?php endif; ?>
			               	<?php endif; ?>
			               	
			               	<?php // Display Event Categories ?>
			                <?php $taxonomy = 'event-category'; ?>
			                <?php $terms = em_get_categories_for($post->ID); ?>
			                <?php if ($terms) : ?>
			                <div class="<?php echo $taxonomy; ?>"><strong><?php _e('Category', 'events-maker'); ?>: </strong>
			                	<?php foreach ($terms as $term) : ?>
			                    	<?php $term_link = get_term_link($term->slug, $taxonomy); ?>
			                        <?php if(is_wp_error($term_link)) continue; ?>
			                    	<a href="<?php echo $term_link; ?>"><?php echo $term->name; ?></a>
			                    <?php endforeach; ?>
			                </div>
			                <?php endif; ?>
			                
			                <?php // Display Event Locations ?>
			                <?php $taxonomy = 'event-location'; ?>
			                <?php $terms = em_get_locations_for($post->ID); ?>
			                <?php if ($terms) : ?>
			                <div class="<?php echo $taxonomy; ?>"><strong><?php _e('Location', 'events-maker'); ?>: </strong>
			                	<?php foreach ($terms as $term) : ?>
			                    	<?php $term_link = get_term_link($term->slug, $taxonomy); ?>
			                        <?php if(is_wp_error($term_link)) continue; ?>
									<a href="<?php echo $term_link; ?>"><?php echo $term->name; ?></a>
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
			                </div>
			                <?php endif; ?>
			                
			                <?php // Display Event Organizers ?>
			                <?php $taxonomy = 'event-organizer'; ?>
			                <?php $terms = em_get_organizers_for($post->ID); ?>
			                <?php if ($terms) : ?>
			                <div class="<?php echo $taxonomy; ?>"><strong><?php _e('Organizer', 'events-maker'); ?>: </strong>
			                	<?php foreach ($terms as $term) : ?>
			                    	<?php $term_link = get_term_link($term->slug, $taxonomy); ?>
			                        <?php if(is_wp_error($term_link)) continue; ?>
			                    	<a href="<?php echo $term_link; ?>"><?php echo $term->name; ?></a>
			                    	<?php // Organizer details
			                    	if ($event_display_options['display_organizer_details'] === 1) : ?>
			                    		<?php $organizer_details = $term->organizer_meta; ?>
			                    		<?php if ($organizer_details) : ?>
				                    		<?php echo !empty($organizer_details['contact_name']) ? $organizer_details['contact_name'] : ''; ?>
				                    		<?php echo !empty($organizer_details['phone']) ? $organizer_details['phone'] : ''; ?>
				                    		<?php echo !empty($organizer_details['email']) ? $organizer_details['email'] : ''; ?>
				                    		<?php echo !empty($organizer_details['website']) ? $organizer_details['website'] : ''; ?>
			                    		<?php endif; ?>
			                    	<?php endif; ?>
			                    <?php endforeach; ?>
			                </div>
			                <?php endif; ?>
		                
		                </div>
	                
	                </header>
	
	                <div class="entry-content">
	                    <?php the_content(); ?>
	                </div>
	    
	                <footer class="entry-meta">
	                    <?php edit_post_link(__('Edit', 'events-maker'), '<span class="edit-link">', '</span>'); ?>
	                </footer>
    
                </article>
                
                <div class="comments-template">
                    <?php comments_template(); ?>
                </div>	

			<?php endwhile; // end of the loop. ?>

		</div>
		
	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>