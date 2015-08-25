<?php
/**
 * Events Maker Template Hooks
 *
 * Action/filter hooks used for Events Maker functions/templates
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */
 
$template = get_option( 'template' );

/**
 * Content wrappers.
 */
add_action( 'em_before_main_content', 'em_output_content_wrapper_start', 10 );
add_action( 'em_after_main_content', 'em_output_content_wrapper_end', 10 );

/**
 * Breadcrumbs.
 */
add_action( 'em_before_main_content', 'em_breadcrumb', 20 );

/**
 * Result count.
 */
add_action( 'em_before_events_loop', 'em_result_count', 10 );

/**
 * Sorting.
 */
add_action( 'em_before_events_loop', 'em_orderby', 20 );

/**
 * Pagination links.
 */
add_action( 'em_after_events_loop', 'em_paginate_links', 10 );

/**
 * Sidebar.
 */
add_action( 'em_get_sidebar', 'em_get_sidebar', 10 );

/**
 * Events archive description.
 */
add_action( 'em_archive_description', 'em_display_location_image', 10 );
add_action( 'em_archive_description', 'em_display_organizer_image', 10 );
add_action( 'em_archive_description', 'em_display_location_details', 20 );
add_action( 'em_archive_description', 'em_display_organizer_details', 20 );
add_action( 'em_archive_description', 'em_display_loop_event_google_map', 30 );
add_action( 'em_archive_description', 'em_event_archive_description', 40 );
add_action( 'em_archive_description', 'em_taxonomy_archive_description', 40 );

/**
 * Event content in the loop.
 */
add_action( 'em_before_loop_event', 'em_display_loop_event_thumbnail', 10 );
add_action( 'em_loop_event_content', 'em_display_event_excerpt', 10 );

add_action( 'em_before_loop_event_title', 'em_display_event_categories', 10 );
add_action( 'em_after_loop_event_title', 'em_display_loop_event_meta', 10 );
add_action( 'em_after_loop_event_title', 'em_display_event_locations', 20 );
add_action( 'em_after_loop_event_title', 'em_display_event_organizers', 30 );
add_action( 'em_loop_event_meta_start', 'em_display_event_date', 10 );
add_action( 'em_after_loop_event', 'em_display_event_tags', 10 );

/**
 * Single event content.
 */
add_action( 'em_before_single_event', 'em_display_single_event_thumbnail', 10 );
add_action( 'em_before_single_event', 'em_display_event_gallery', 20 );
add_action( 'em_single_event_content', 'em_display_event_content', 10 );

add_action( 'em_before_single_event_title', 'em_display_event_categories', 10 );
add_action( 'em_after_single_event_title', 'em_display_single_event_meta', 10 );
add_action( 'em_after_single_event_title', 'em_display_event_locations', 20 );
add_action( 'em_after_single_event_title', 'em_display_event_organizers', 30 );
add_action( 'em_after_single_event_title', 'em_display_single_event_google_map', 40 );
add_action( 'em_after_single_event_title', 'em_display_event_tickets', 50 );
add_action( 'em_single_event_meta_start', 'em_display_single_event_date', 10 );
add_action( 'em_after_single_event', 'em_display_ical_button', 10 );
add_action( 'em_after_single_event', 'em_display_event_tags', 20 );

/**
 * Widget event content.
 */
add_action( 'em_before_widget_event_title', 'em_display_widget_event_date', 10 );

/**
 * Locations archive.
 */
add_action( 'em_before_event_locations_loop', 'em_display_loop_event_google_map', 10 );

/**
 * Location content in the loop.
 */
add_action( 'em_before_loop_event_location_title', 'em_display_location_image', 10 );
add_action( 'em_after_loop_event_location_title', 'em_display_location_details', 10 );

/**
 * Organizer content in the loop.
 */
add_action( 'em_before_loop_event_organizer_title', 'em_display_organizer_image', 10 );
add_action( 'em_after_loop_event_organizer_title', 'em_display_organizer_details', 10 );
