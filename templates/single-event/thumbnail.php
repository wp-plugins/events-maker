<?php
/**
 * Single event thumbnail
 * 
 * Override this template by copying it to yourtheme/single-event/thumbnail.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post;

// event thumbnail
if ( ! post_password_required() && has_post_thumbnail() ) :

	$image_title	= apply_filters( 'em_single_event_thumbnail_title', esc_attr( get_the_title( get_post_thumbnail_id() ) ) );
	$image_link		= apply_filters( 'em_single_event_thumbnail_link', wp_get_attachment_url( get_post_thumbnail_id() ) );
	$size			= apply_filters( 'em_single_event_thumbnail_size', 'post-thumbnail' );
	$attr			= apply_filters( 'em_single_event_thumbnail_attr', array( 'title' => $image_title ) );
	$image			= get_the_post_thumbnail( $post->ID, $size, $attr );

	echo apply_filters( 'em_single_event_thumbnail_html', sprintf( '<a href="%s" class="post-thumbnail entry-thumbnail" title="%s" rel="lightbox">%s</a>', $image_link, $image_title, $image ), $post->ID );

endif;