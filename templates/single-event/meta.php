<?php
/**
 * The template for single event meta.
 * 
 * Override this template by copying it to yourtheme/single-event/meta.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

global $post;
?>

<div class="entry-meta">

	<?php
	/**
	 * em_single_event_meta_start hook
	 * 
	 * @hooked em_display_single_event_date - 10
	 */
	do_action( 'em_single_event_meta_start' );
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
	 * em_single_event_meta_end hook
	 */
	do_action( 'em_single_event_meta_end' );
	?>

</div>