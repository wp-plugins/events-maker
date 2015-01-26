<?php
/**
 * The template for displaying event content in the single-event.php template
 *
 * Override this template by copying it to yourtheme/content-single-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Extra event classes
$classes = apply_filters('em_single_event_classes', array('hcalendar'));

?>

	<article id="post-<?php the_ID(); ?>" <?php post_class($classes); ?>>
		
		<?php
		/**
		 * em_before_single_event hook
		 * 
		 * @hooked em_display_single_event_thumbnail - 10
		 * @hooked em_display_event_gallery - 20
		 */
		do_action('em_before_single_event');
		?>
	
	    <header class="entry-header">
	    	
	    	<?php
			/**
			 * em_before_single_event_title hook
			 * 
			 * @hooked em_display_event_categories - 10
			 */
			do_action ('em_before_single_event_title');
			?>
			
			<h1 class="entry-title summary">
				
				<?php the_title(); ?>
				
			</h1>
			
			<?php
			/**
			 * em_after_single_event_title hook
			 * 
			 * @hooked em_display_single_event_meta - 10
			 * @hooked em_display_event_locations - 20
			 * @hooked em_display_event_organizers - 30
			 * @hooked em_display_google_map - 40
			 * @hooked em_display_event_tickets - 50
			 */
			do_action ('em_after_single_event_title');
			?>

	    </header>
	    
	    <?php
		/**
		 * em_single_event_content hook
		 * 
		 * @hooked em_display_event_content - 10
		 */
		do_action('em_single_event_content');
		?>
	    
	    <?php
		/**
		 * em_after_single_event hook
		 */
		do_action('em_after_single_event');
		?>

	</article>