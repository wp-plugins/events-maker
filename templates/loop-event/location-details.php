<?php
/**
 * Event location details
 * 
 * Override this template by copying it to yourtheme/loop-event/location-details.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $location;

// get the term if not set
$location = empty( $location ) ? em_get_location() : $location;
?>

<div class="archive-meta entry-meta">

	<?php if ( ! empty( $location ) && ! is_wp_error( $location ) ) : ?>

		<?php
		// location fields
		$location_fields	= em_get_event_location_fields();
		$location_details	= apply_filters( 'em_loop_event_location_details', (isset( $location->location_meta ) ? $location->location_meta : '' ) );

		if ( ! empty( $location_fields ) && ! empty( $location_details ) ) :

			foreach ( $location_fields as $key => $field ) :

				// field value
				$field['value'] = ! empty( $location_details[$key] ) ? $location_details[$key] : '';
				// field
				echo em_event_taxonomy_field( $key, $field );

			endforeach;

		endif;

	endif;
	?>

</div>