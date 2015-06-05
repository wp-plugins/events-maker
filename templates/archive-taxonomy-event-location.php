<?php
/**
 * The template for displaying event locations list
 *
 * Override this template by copying it to yourtheme/archive-taxonomy-event-location.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly
	
// get locations
$locations = em_get_locations();
?>

<?php
// start the loop
if ( $locations ) :

	// setup object
	global $location;
	?>

	<?php
	/**
	 * em_before_event_locations_loop hook
	 * 
	 * @hooked em_display_loop_event_google_map - 10
	 */
	do_action( 'em_before_event_locations_loop' );
	?>

	<?php foreach ( $locations as $location ) : ?>

		<?php em_get_template_part( 'content-taxonomy', 'event-location' ); ?>

	<?php endforeach; ?>

	<?php
	/**
	 * em_after_event_locations_loop hook
	 * 
	 */
	do_action( 'em_after_event_locations_loop' );
	?>

<?php else : ?>

	<article id="term-0" class="term no-results not-found">

		<div class="entry-content">

			<p><?php _e( 'Apologies, but no event locations were found.', 'events-maker' ); ?></p>

		</div>

	</article>

<?php endif; ?>