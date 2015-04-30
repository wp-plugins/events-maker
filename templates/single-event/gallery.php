<?php
/**
 * Event gallery
 * 
 * Override this template by copying it to yourtheme/single-event/gallery.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.4.4
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post;

// display options
$display_options = get_post_meta( $post->ID, '_event_display_options', true );

// gallery enabled?
if ( ! isset( $display_options['display_gallery'] ) || ! $display_options['display_gallery'] )
	return;

$columns = apply_filters( 'em_event_gallery_columns', 4 );
$count = 0;

// event gallery
if ( ! post_password_required() && metadata_exists( 'post', $post->ID, '_event_gallery' ) ) :

	$event_gallery = get_post_meta( $post->ID, '_event_gallery', true );

	if ( ! empty( $event_gallery ) ) :

		echo '<div class="event-gallery columns-' . $columns . '">';

		$images = array_filter( explode( ',', $event_gallery ) );

		foreach ( $images as $image_id ) :

			$count ++;

			// enable additional classes
			$classes = apply_filters( 'em_event_gallery_classes', array( 'thumbnail', 'thumbnail_id-' . $image_id ) );

			if ( ($count - 1) % $columns == 0 || $columns == 1 )
				$classes[] = 'first';
			if ( $count % $columns == 0 )
				$classes[] = 'last';

			$image_title	= apply_filters( 'em_event_gallery_thumbnail_title', esc_attr( get_the_title( $image_id ) ) );
			$image_link		= apply_filters( 'em_event_gallery_thumbnail_link', wp_get_attachment_url( $image_id ) );
			$size			= apply_filters( 'em_event_gallery_thumbnail_size', 'thumbnail' );
			$attr			= apply_filters( 'em_event_gallery_thumbnail_attr', array( 'title' => $image_title ) );
			$image			= wp_get_attachment_image( $image_id, $size, false, $attr );

			echo apply_filters( 'em_event_gallery_thumbnail_html', sprintf( '<a href="%s" class="event-thumbnail ' . implode( ' ', $classes ) . '" title="%s" rel="lightbox">%s</a>', $image_link, $image_title, $image ), $post->ID );

		endforeach;

		echo '</div>';

	endif;

endif;