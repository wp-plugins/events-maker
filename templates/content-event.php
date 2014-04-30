<?php
/**
 * The template for displaying event content within loops.
 *
 * Override this template by copying it to yourtheme/content-event.php or yourtheme/events-maker/content-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('hcalendar'); ?>>
		
		<?php // Event thumbnail
		if (!post_password_required() || has_post_thumbnail() ) { ?>
			<a class="post-thumbnail entry-thumbnail" href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail(); ?>
			</a>
		<?php } ?>
	
	    <header class="entry-header">

	    	<?php // Display event categories
	    	em_display_event_categories(); ?>

	        <?php // Display the title ?>
	        <h1 class="entry-title summary"><a href="<?php the_permalink(); ?>" class="url" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
	        
	        <div class="entry-meta">
	        	
				<?php // Event date
				em_display_event_date(); ?>
	
				<?php // Comments link
				if (!post_password_required() && (comments_open() || get_comments_number())) : ?>
					<span class="comments-link"><?php comments_popup_link(__('Leave a comment', 'events-maker' ), __('1 Comment', 'events-maker'), __('% Comments', 'events-maker')); ?></span>
				<?php endif; ?>
	
				<?php // Edit link
				edit_post_link(__('Edit', 'events-maker'), '<span class="edit-link">', '</span>'); ?>
				
			</div>
			
			<?php // Display event locations
			em_display_event_locations(); ?>
	
	        <?php // Display event organizers
	        em_display_event_organizers(); ?>

	    </header>
	    
	    <div class="entry-summary description">

	        <?php the_excerpt(); // Event excerpt ?>
	        
	    </div>

		<?php // Display event tags
		em_display_event_tags(); ?>
	
	</article>