<?php
/**
 * The template for displaying event content in the single-event.php template
 *
 * Override this template by copying it to yourtheme/content-single-event.php or yourtheme/events-maker/content-single-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly
 
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('hcalendar'); ?>>
		
		<?php // Event thumbnail
		if (!post_password_required() && has_post_thumbnail() ) { ?>
			<div class="post-thumbnail entry-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div>
		<?php } ?>
	
	    <header class="entry-header">
	    	
	    	<?php // Display event categories
	    	em_display_event_categories(); ?>
			
			<?php // Display the title ?>
	        <h1 class="entry-title summary"><?php the_title(); ?></h1>
	        
	        <div class="entry-meta">
				
				<?php // Event date
				if (em_is_recurring()) : // is recurring?
					em_display_event_occurrences(); // display occurrences date
				else :
					em_display_event_date(); // display event date
				endif; ?>

				<?php // Comments link
				if (!post_password_required() && (comments_open() || get_comments_number())) : ?>
					<span class="comments-link"><?php comments_popup_link(__('Leave a comment', 'events-maker' ), __('1 Comment', 'events-maker'), __('% Comments', 'events-maker')); ?></span>
				<?php endif; ?>

				<?php // Edit link
				edit_post_link(__('Edit', 'events-maker'), '<span class="edit-link">', '</span>'); ?>
				
			</div>

			<?php // Get event display options
			$event_display_options = get_post_meta($post->ID, '_event_display_options', TRUE); 
			$event_locations = em_get_locations_for($post->ID); ?>

			<?php // Display Google Map
	        if ($event_display_options['google_map'] === 1 && (isset($event_locations) && !empty($event_locations))) : // if option enabled and any location is set for the event
	        	em_display_google_map(); 
	        endif; ?>

           	<?php // Display tickets details
            if ($event_display_options['price_tickets_info'] === 1) : // if option enabled  
            	em_display_event_tickets();
           	endif; ?>
           	
           	<?php // Display event locations
	    	em_display_event_locations(); ?>
	    	
	    	<?php // Display event organizers
	    	em_display_event_organizers(); ?>

	    </header>
	
	    <div class="entry-content description">
	        <?php the_content(); ?>
	    </div>
		
		<?php // Display event tags
	    em_display_event_tags(); ?>

	</article>