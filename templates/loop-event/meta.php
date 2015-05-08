<?php
/**
 * The template for event meta within loops.
 * 
 * Override this template by copying it to yourtheme/loop-event/meta.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post;
?>

<div class="entry-meta">

	<?php
	/**
	 * em_loop_event_meta_start hook
	 * 
	 * @hooked em_display_event_date - 10
	 */
	do_action( 'em_loop_event_meta_start' );
	?>

	<?php // comments link
	if ( ! post_password_required() && (comments_open() || get_comments_number()) ) :
		?>
		<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'events-maker' ), __( '1 Comment', 'events-maker' ), __( '% Comments', 'events-maker' ) ); ?></span>

	<?php endif; ?>

	<?php // edit link
	edit_post_link( __( 'Edit', 'events-maker' ), '<span class="edit-link">', '</span>' );
	?>

	<?php
	/**
	 * em_loop_event_meta_end hook
	 */
	do_action( 'em_loop_event_meta_end' );
	?>

</div>