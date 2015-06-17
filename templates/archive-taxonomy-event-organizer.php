<?php
/**
 * The template for displaying event organizers list
 *
 * Override this template by copying it to yourtheme/archive-taxonomy-event-organizer.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly
	
// get organizers
$organizers = em_get_organizers();
?>

<?php
// start the loop
if ( $organizers ) :

	// setup object
	global $organizer;
	?>

	<?php
	/**
	 * em_before_event_organizers_loop hook
	 */
	do_action( 'em_before_event_organizers_loop' );
	?>

	<?php foreach ( $organizers as $organizer ) : ?>

		<?php em_get_template_part( 'content-taxonomy', 'event-organizer' ); ?>

	<?php endforeach; ?>

	<?php
	/**
	 * em_after_event_organizers_loop hook
	 * 
	 */
	do_action( 'em_after_event_organizers_loop' );
	?>

<?php else : ?>

	<article id="term-0" class="term no-results not-found">

		<div class="entry-content">

			<p><?php _e( 'Apologies, but no event organizers were found.', 'events-maker' ); ?></p>

		</div>

	</article>

<?php endif; ?>