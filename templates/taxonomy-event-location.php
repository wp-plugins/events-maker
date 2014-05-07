<?php
/**
 * The template for displaying event location archives.
 *
 * Override this template by copying it to yourtheme/taxonomy-event-location.php or yourtheme/events-maker/taxonomy-event-location.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header(); ?>

	<section id="primary" class="content-area">

		<div id="content" class="site-content" role="main">

		<?php if (have_posts()) : ?>

			<header class="archive-header">

				<h1 class="archive-title"><?php printf(__('Events Location: %s', 'events-maker'), single_term_title('', false)); ?></h1>

                <?php em_display_google_map(); // Display Google Map ?>
                
                <div class="archive-meta entry-meta">

	                <?php // Display additional location info ?>
	                <?php $location = em_get_location(); ?>
	                <?php $location_details = $location->location_meta; ?>
	                <?php if (!empty($location_details['address'])) : ?>
	                	<div class="location-address"><strong><?php echo __('Address', 'events-maker'); ?>:</strong> <?php echo $location_details['address']; ?></div>
	                <?php endif; ?>
	                <?php if (!empty($location_details['zip'])) : ?>
	                	<div class="location-zip"><strong><?php echo __('Zip Code', 'events-maker'); ?>:</strong> <?php echo $location_details['zip']; ?></div>
	                <?php endif; ?>
	                <?php if (!empty($location_details['city'])) : ?>
	                	<div class="location-city"><strong><?php echo __('City', 'events-maker'); ?>:</strong> <?php echo $location_details['city']; ?></div>
	                <?php endif; ?>
	                <?php if (!empty($location_details['state'])) : ?>
	                	<div class="location-state"><strong><?php echo __('State / Province', 'events-maker'); ?>:</strong> <?php echo $location_details['state']; ?></div>
	                <?php endif; ?>        
	                <?php if (!empty($location_details['country'])) : ?>
	                	<div class="location-country"><strong><?php echo __('Country', 'events-maker'); ?>:</strong> <?php echo $location_details['country']; ?></div>
	                <?php endif; ?>

				</div>

				<?php // Show an optional term description.
				$term_description = term_description();
				if (!empty($term_description)) :
					printf('<div class="archive-description taxonomy-description">%s</div>', $term_description);
				endif; ?>

			</header>

			<?php // Start the Loop
			while (have_posts()) : the_post(); ?>
				
				<?php em_get_template_part('content', 'event'); ?>

			<?php endwhile; ?>

			<?php // Pagination
            if ($wp_query->max_num_pages > 1) : ?>

                <nav id="nav-below" class="navigation paging-navigation" role="navigation">
                	
                	<div class="pagination loop-pagination">

                    	<?php em_paginate_links(); ?>
                    	
                   	</div>

                </nav>

            <?php endif; ?>

		<?php else : ?>

            <?php em_get_template_part('content', 'event-none'); ?>

        <?php endif; ?>

		</div>

	</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>