<?php
/**
 * Event organizer image
 * 
 * Override this template by copying it to yourtheme/loop-event/organizer-image.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $organizer;

// get the term if not set
$organizer = empty( $organizer ) ? em_get_organizer() : $organizer;

// image
if ( ! empty( $organizer->organizer_meta['image'] ) ) :

	$image_title	= apply_filters( 'em_loop_event_organizer_thumbnail_title', esc_html( $organizer->name ) );
	$image_link		= apply_filters( 'em_loop_event_organizer_thumbnail_link', get_term_link( absint( $organizer->term_id ), esc_attr( $organizer->taxonomy ) ) );
	$size			= apply_filters( 'em_loop_event_organizer_thumbnail_size', 'post-thumbnail' );
	$attr			= apply_filters( 'em_loop_event_organizer_thumbnail_attr', array( 'title' => $image_title ) );
	$image			= wp_get_attachment_image( $organizer->organizer_meta['image'], $size, $attr );

	echo apply_filters( 'em_loop_event_organizer_thumbnail_html', sprintf( '<a href="%s" class="post-thumbnail term-thumbnail" title="%s" rel="bookmark">%s</a>', $image_link, $image_title, $image ), $organizer );

endif;