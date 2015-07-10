<?php
/**
 * Events breadcrumb
 * 
 * Override this template by copying it to yourtheme/global/breadcrumb.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post, $wp_query;

$home_label = apply_filters( 'em_breadcrumbs_home_label', get_bloginfo( 'name' ) ); // text for the 'Home' link
$delimiter = apply_filters( 'em_breadcrumbs_delimiter', ' &raquo; ' ); // delimiter between crumbs
$wrap_before = apply_filters( 'em_breadcrumbs_wrap_before', '<nav class="navigation breadcrumb-navigation" role="navigation"><p class="loop-breadcrumbs breadcrumbs">' );
$wrap_after = apply_filters( 'em_breadcrumbs_wrap_after', '</p></nav>' );
$before = apply_filters( 'em_breadcrumbs_current_before', '' ); // before the crumb
$after = apply_filters( 'em_breadcrumbs_current_after', '' ); // after the crumb

echo $wrap_before;

echo $before . '<a class="home" href="' . apply_filters( 'em_breadcrumbs_home_url', home_url() ) . '">' . $home_label . '</a>' . $after . $delimiter;

if ( is_category() ) :

	$cat_obj = $wp_query->get_queried_object();
	$this_category = get_category( $cat_obj->term_id );

	if ( $this_category->parent != 0 ) :

		$parent_category = get_category( $this_category->parent );
		echo get_category_parents( $parent_category, true, $delimiter );

	endif;

	echo $before . single_cat_title( '', false ) . $after;

elseif ( is_tax() ) :

	$current_term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
	$ancestors = array_reverse( get_ancestors( $current_term->term_id, get_query_var( 'taxonomy' ) ) );

	if ( $ancestors ) :

		foreach ( $ancestors as $ancestor ) :

			$ancestor = get_term( $ancestor, get_query_var( 'taxonomy' ) );
			echo $before . '<a href="' . get_term_link( $ancestor->slug, get_query_var( 'taxonomy' ) ) . '">' . esc_html( $ancestor->name ) . '</a>' . $after . $delimiter;

		endforeach;

	endif;

	if ( is_tax( 'event-category' ) || is_tax( 'event-organizer' ) || is_tax( 'event-location' ) || is_tax( 'event-tag' ) ) :

		$post_type = get_post_type_object( 'event' );
		$name = $post_type->labels->name;
		echo $before . '<a href="' . get_post_type_archive_link( 'event' ) . '">' . $name . '</a> ' . $after . $delimiter;

	endif;

	echo $before . esc_html( $current_term->name ) . $after;

elseif ( is_day() ) :

	echo $before . '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a>' . $after . $delimiter;
	echo $before . '<a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '">' . get_the_time( 'F' ) . '</a>' . $after . $delimiter;
	echo $before . get_the_time( 'd' ) . $after;

elseif ( is_month() ) :

	echo $before . '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a>' . $after . $delimiter;
	echo $before . get_the_time( 'F' ) . $after;

elseif ( is_year() ) :

	echo $before . get_the_time( 'Y' ) . $after;

elseif ( is_post_type_archive() ) :

	$post_type = get_post_type_object( 'event' );
	$name = $post_type->labels->name;

	if ( is_search() )
		echo $before . '<a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $name . '</a>' . $delimiter . __( 'Search results for &ldquo;', 'events-maker' ) . get_search_query() . '&rdquo;' . $after;
	elseif ( em_is_event_archive() && get_query_var( 'event_ondate' ) )
		echo $before . '<a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $name . '</a>' . $delimiter . get_query_var( 'event_ondate' ) . $after;
	elseif ( is_paged() )
		echo $before . '<a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $name . '</a>' . $after;
	else
		echo $before . $name . $after;

elseif ( is_single() && ! is_attachment() ) :

	if ( get_post_type() != 'post' ) :

		$post_type = get_post_type_object( get_post_type() );
		$slug = $post_type->rewrite;
		echo $before . '<a href="' . get_post_type_archive_link( get_post_type() ) . '">' . $post_type->labels->name . '</a>' . $after . $delimiter;

		if ( get_post_type() == 'event' ) :

			if ( $terms = wp_get_post_terms( $post->ID, 'event-category', array( 'orderby' => 'parent', 'order' => 'DESC' ) ) ) :

				$main_term = $terms[0];
				$ancestors = get_ancestors( $main_term->term_id, 'event-category' );
				$ancestors = array_reverse( $ancestors );

				foreach ( $ancestors as $ancestor ) :

					$ancestor = get_term( $ancestor, 'event-category' );

					if ( ! is_wp_error( $ancestor ) && $ancestor )
						echo $before . '<a href="' . get_term_link( $ancestor->slug, 'event-category' ) . '">' . $ancestor->name . '</a>' . $after . $delimiter;

				endforeach;

				echo $before . '<a href="' . get_term_link( $main_term->slug, 'event-category' ) . '">' . $main_term->name . '</a>' . $after . $delimiter;

			endif;

		endif;

	else :

		$cat = current( get_the_category() );
		echo get_category_parents( $cat, true, $delimiter );

	endif;

	echo $before . get_the_title() . $after;

elseif ( is_404() ) :

	echo $before . __( 'Error 404', 'events-maker' ) . $after;

elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' ) :

	$post_type = get_post_type_object( get_post_type() );

	if ( $post_type ) :
		echo $before . $post_type->labels->name . $after;
	endif;

elseif ( is_attachment() ) :

	$parent = get_post( $post->post_parent );
	$cat = get_the_category( $parent->ID );
	$cat = $cat[0];
	echo get_category_parents( $cat, true, '' . $delimiter );
	echo $before . '<a href="' . get_permalink( $parent ) . '">' . $parent->post_title . '</a>' . $after . $delimiter;
	echo $before . get_the_title() . $after;

elseif ( is_page() && ! $post->post_parent ) :

	echo $before . get_the_title() . $after;

elseif ( is_page() && $post->post_parent ) :

	$parent_id = $post->post_parent;
	$breadcrumbs = array();

	while ( $parent_id ) :

		$page = get_page( $parent_id );
		$breadcrumbs[] = '<a href="' . get_permalink( $page->ID ) . '">' . get_the_title( $page->ID ) . '</a>';
		$parent_id = $page->post_parent;

	endwhile;

	$breadcrumbs = array_reverse( $breadcrumbs );

	foreach ( $breadcrumbs as $crumb )
		echo $crumb . '' . $delimiter;

	echo $before . get_the_title() . $after;

elseif ( is_search() ) :

	echo $before . __( 'Search results for &ldquo;', 'events-maker' ) . get_search_query() . '&rdquo;' . $after;

elseif ( is_tag() ) :

	echo $before . __( 'Posts tagged &ldquo;', 'events-maker' ) . single_tag_title( '', false ) . '&rdquo;' . $after;

elseif ( is_author() ) :

	$userdata = get_userdata( $author );
	echo $before . __( 'Author:', 'events-maker' ) . ' ' . $userdata->display_name . $after;

endif;

if ( get_query_var( 'paged' ) )
	echo ' (' . __( 'Page', 'events-maker' ) . ' ' . get_query_var( 'paged' ) . ')';

echo $wrap_after;