<?php
/**
 * The template for displaying event organizer content within loops.
 *
 * Override this template by copying it to yourtheme/content-taxonomy-event-organizer.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly;

global $organizer;

// extra event classes
$classes = apply_filters( 'em_loop_event_organizer_classes', array( 'vcard', 'term', 'term-' . esc_attr( $organizer->taxonomy ), 'term-' . absint( $organizer->term_id ), 'term-' . esc_attr( $organizer->slug ) ) );
?>

<article id="term-<?php echo absint( $organizer->term_id ); ?>" class="<?php echo implode( ' ', $classes ); ?>">

	<?php
	/**
	 * em_before_loop_event_organizer hook
	 */
	do_action( 'em_before_loop_event_organizer' );
	?>

	<header class="entry-header">

		<?php
		/**
		 * em_before_loop_event_organizer_title hook
		 */
		do_action( 'em_before_loop_event_organizer_title' );
		?>

		<h3 class="entry-title summary">

			<a href="<?php echo get_term_link( absint( $organizer->term_id ), esc_attr( $organizer->taxonomy ) ); ?>" class="url" title="<?php echo esc_html( $organizer->name ); ?>"><?php echo esc_html( $organizer->name ); ?></a>

		</h3>

		<?php
		/**
		 * em_after_loop_event_title hook
		 * 
		 * @hooked em_display_organizer_details - 10
		 */
		do_action( 'em_after_loop_event_organizer_title' );
		?>

	</header>

	<div class="entry-content description">

		<?php echo apply_filters( 'em_loop_event_organizer_excerpt', wp_trim_words( strip_shortcodes( wp_kses_post( $organizer->description ) ) ) ); ?>

	</div>

	<?php
	/**
	 * em_after_loop_event_organizer hook
	 */
	do_action( 'em_after_loop_event_organizer' );
	?>

</article>