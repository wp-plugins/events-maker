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
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// locations
$locations = em_get_location(get_queried_object_id());

// locations available?
if (!isset($locations) || empty($locations))
	return;

?>

<?php
$args = apply_filters('em_loop_event_google_map_args', array(
	'width' => '100%',
	'height' => '200px',
	'zoom' => 15,
	'maptype' => 'roadmap',
	'maptypecontrol' => true,
	'zoomcontrol' => true,
	'streetviewcontrol' => true,
	'overviewmapcontrol' => false,
	'pancontrol' => false,
	'rotatecontrol' => false,
	'scalecontrol' => false,
	'draggable' => true,
	'keyboardshortcuts' => true,
	'scrollzoom' => true
));

em_display_google_map($args, $locations);
?>