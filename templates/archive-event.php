<?php
/**
 * The template for displaying event archives.
 *
 * Override this template by copying it to yourtheme/archive-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header('events'); ?>

	<?php
	/**
	 * em_before_main_content hook
	 *
	 * @hooked em_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked em_breadcrumbs - 20
	 */
	do_action('em_before_main_content');
	?>
	
		<header class="archive-header page-header">
			
			<?php if (apply_filters('em_show_page_title', true)) : ?>

				<h1 class="archive-title page-title"><?php em_page_title(); ?></h1>

			<?php endif; ?>
			
			<?php 
			/**
			 * em_archive_description hook
			 *
			 * @hooked em_display_loop_event_google_map - 10
			 * @hooked em_display_location_info - 20
			 * @hooked em_display_organizer_info - 20
			 * @hooked em_taxonomy_archive_description - 30
			 */
			do_action('em_archive_description');
			?>

		</header>

		<?php // start the loop
		if (have_posts()) : ?>
			
			<?php
			/**
			 * em_before_events_loop hook
			 */
			do_action('em_before_events_loop');
			?>

			<?php
			while (have_posts()) : the_post(); ?>
				
				<?php em_get_template_part('content', 'event'); ?>

			<?php endwhile; ?>
			
			<?php
			/**
			 * em_after_events_loop hook
			 * 
			 * @hooked em_paginate_links - 10
			 */
			do_action('em_after_events_loop');
			?>

		<?php else : ?>

            <article id="post-0" class="post no-results not-found">
			
			    <div class="entry-content">
			    	
			        <p><?php _e('Apologies, but no events were found.', 'events-maker'); ?></p>
			        
			    </div>
			
			</article>

        <?php endif; ?>

	<?php
	/**
	 * em_after_main_content hook
	 *
	 * @hooked em_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action('em_after_main_content');
	?>

	<?php
	/**
	 * em_get_sidebar hook
	 *
	 * @hooked em_get_sidebar - 10
	 */
	do_action('em_get_sidebar');
	?>
	
<?php get_footer('events'); ?>