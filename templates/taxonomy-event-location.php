<?php get_header(); ?>

	<section id="primary" class="site-content">

		<div id="content" role="main">

		<?php if (have_posts()) : ?>

			<header class="archive-header">

				<h1 class="archive-title"><?php printf(__('Events Location: %s', 'events-maker'), single_term_title('', false)); ?></h1>

                <?php em_display_google_map(); ?>

                <div class="archive-meta">

	                <?php // Display additional location info ?>
	                <?php $location = em_get_location(); ?>
	                <?php $location_details = $location->location_meta; ?>
	                <?php if ($location_details['address']) : ?>
	                	<div class="location-address"><strong><?php echo __('Address', 'events-maker'); ?>:</strong> <?php echo $location_details['address']; ?></div>
	                <?php endif; ?>
	                <?php if ($location_details['zip']) : ?>
	                	<div class="location-zip"><strong><?php echo __('Zip Code', 'events-maker'); ?>:</strong> <?php echo $location_details['zip']; ?></div>
	                <?php endif; ?>
	                <?php if ($location_details['city']) : ?>
	                	<div class="location-city"><strong><?php echo __('City', 'events-maker'); ?>:</strong> <?php echo $location_details['city']; ?></div>
	                <?php endif; ?>
	                <?php if ($location_details['state']) : ?>
	                	<div class="location-state"><strong><?php echo __('State / Province', 'events-maker'); ?>:</strong> <?php echo $location_details['state']; ?></div>
	                <?php endif; ?>        
	                <?php if ($location_details['country']) : ?>
	                	<div class="location-country"><strong><?php echo __('Country', 'events-maker'); ?>:</strong> <?php echo $location_details['country']; ?></div>
	                <?php endif; ?>

				</div>

				<?php if (term_description()) : // Show an optional term description ?>
					<div class="archive-meta">
						<?php echo term_description(); ?>
					</div>
				<?php endif; ?>

			</header>

			<?php // Start the Loop ?>
			<?php while (have_posts()) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('vevent'); ?>>
                	
                    <header class="entry-header">

	                    <?php // Display the title ?>
	                    <h1 class="entry-title summary"><a href="<?php the_permalink(); ?>" class="url" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
	
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
	                        <?php endforeach; ?>
	                    </div>
	                    <?php endif; ?>

                    </header>

                    <div class="entry-summary">
                    	<?php // If it has one, display the thumbnail
						if( has_post_thumbnail() )
							the_post_thumbnail('thumbnail', array('style'=>'float:left; margin-right:20px;')); ?>
                        <?php the_excerpt(); ?>
                    </div>

                    <footer class="entry-meta">
                    	
                    	<?php if (taxonomy_exists('event-tag')) : ?>
		                    <div class="entry-tags">
		                        <?php $tag_list = get_the_term_list( get_the_ID(), 'event-tag', __('<strong>Tags: </strong>', 'events-maker'), ', ', ''); ?>
		                        <?php if ($tag_list) : ?>
		                        	<?php printf($tag_list); ?>
								<?php endif; ?>
		                    </div>
	                    <?php endif; ?>
                    	
                        <?php edit_post_link(__('Edit', 'events-maker'), '<span class="edit-link">', '</span>'); ?>

                        <?php if (comments_open()) : ?>
                            <span class="comments-link"><?php comments_popup_link('<span class="leave-reply">' . __('Leave a reply', 'events-maker') . '</span>', __('1 Reply', 'events-maker'), __('% Replies', 'events-maker')); ?></span>
                   		<?php endif; ?>
                   		
                    </footer>

                </article>

			<?php endwhile; ?>

			<?php // Pagination
            if ($wp_query->max_num_pages > 1) : ?>
            
                <nav id="nav-below">
                	
                    <div class="nav-next"><?php next_posts_link(__('Next events <span class="meta-nav">&rarr;</span>' , 'events-maker')); ?></div>
                    <div class="nav-previous"><?php previous_posts_link(__(' <span class="meta-nav">&larr;</span> Previous events', 'events-maker')); ?></div>
                    
                </nav>
                
            <?php endif; ?>

		<?php else : ?>

            <article id="post-0" class="post no-results not-found">
            	
                <header class="entry-header">
                    <h1 class="entry-title"><?php _e('No Events Found', 'events-maker'); ?></h1>
                </header>

                <div class="entry-content">
                    <p><?php _e('Apologies, but no events were found.', 'events-maker'); ?></p>
                </div>
                
            </article>

        <?php endif; ?>

		</div>
		
	</section>

<?php get_sidebar(); ?>
