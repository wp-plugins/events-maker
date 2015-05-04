<?php
/**
 * The template for displaying all single events.
 *
 * Override this template by copying it to yourtheme/single-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

get_header( 'events' );
?>

<?php
/**
 * em_before_main_content hook
 *
 * @hooked em_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked em_breadcrumbs - 20
 */
do_action( 'em_before_main_content' );
?>

<?php // start the loop
while ( have_posts() ) : the_post();
	?>

	<?php em_get_template_part( 'content', 'single-event' ); ?>

	<?php
	// if comments are open or we have at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;
	?>

<?php endwhile; ?>

<?php
/**
 * em_after_main_content hook
 *
 * @hooked em_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'em_after_main_content' );
?>

<?php
/**
 * em_get_sidebar hook
 *
 * @hooked em_get_sidebar - 10
 */
do_action( 'em_get_sidebar' );
?>

<?php get_footer( 'events' ); ?>