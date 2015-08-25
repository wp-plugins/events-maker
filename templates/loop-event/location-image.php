<?php
/**
 * Event location image
 * 
 * Override this template by copying it to yourtheme/loop-event/location-image.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $location;

// get the term if not set
$location = empty( $location ) ? em_get_location() : $location;

// image
if ( ! empty( $location->location_meta['image'] ) ) :

	$image_title	= apply_filters( 'em_loop_event_location_thumbnail_title', esc_html( $location->name ) );
	$image_link		= apply_filters( 'em_loop_event_location_thumbnail_link', get_term_link( absint( $location->term_id ), esc_attr( $location->taxonomy ) ) );
	$size			= apply_filters( 'em_loop_event_location_thumbnail_size', 'post-thumbnail' );
	$attr			= apply_filters( 'em_loop_event_location_thumbnail_attr', array( 'title' => $image_title ) );
	$image			= wp_get_attachment_image( $location->location_meta['image'], $size, $attr );

	echo apply_filters( 'em_loop_event_location_thumbnail_html', sprintf( '<a href="%s" class="post-thumbnail term-thumbnail" title="%s" rel="bookmark">%s</a>', $image_link, $image_title, $image ), $location );

endif;