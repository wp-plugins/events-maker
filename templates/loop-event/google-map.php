<?php
/**
 * The template for location google map
 * 
 * Override this template by copying it to yourtheme/loop-event/google-map.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly
	
// locations
if ( is_tax() )
// single location page
	$locations = em_get_location( (int) get_queried_object_id() );
else
// locations archive page
	$locations = em_get_locations();

// locations available?
if ( empty( $locations ) )
	return;

$args = apply_filters( 'em_loop_event_google_map_args', array(
	'width'				 => '100%',
	'height'			 => '300px',
	'zoom'				 => 15,
	'maptype'			 => 'roadmap',
	'maptypecontrol'	 => true,
	'zoomcontrol'		 => true,
	'streetviewcontrol'	 => true,
	'overviewmapcontrol' => false,
	'pancontrol'		 => false,
	'rotatecontrol'		 => false,
	'scalecontrol'		 => false,
	'draggable'			 => true,
	'keyboardshortcuts'	 => true,
	'scrollzoom'		 => true
	) );

em_display_google_map( $args, $locations );