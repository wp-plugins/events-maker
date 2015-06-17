<?php
/**
 * Event organizer details
 * 
 * Override this template by copying it to yourtheme/loop-event/organizer-details.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $organizer;

// get the term if not set
$organizer = empty( $organizer ) ? em_get_organizer() : $organizer;
?>

<div class="archive-meta entry-meta">

	<?php if ( ! empty( $organizer ) && ! is_wp_error( $organizer ) ) : ?>

		<?php
		// organizer fields
		$organizer_fields	= em_get_event_organizer_fields();
		$organizer_details	= apply_filters( 'em_loop_event_organizer_details', (isset( $organizer->organizer_meta ) ? $organizer->organizer_meta : '' ) );

		if ( ! empty( $organizer_fields ) && ! empty( $organizer_details ) ) :

			foreach ( $organizer_fields as $key => $field ) :

				// field value
				$field['value'] = ! empty( $organizer_details[$key] ) ? $organizer_details[$key] : '';
				// field
				echo em_event_taxonomy_field( $key, $field );

			endforeach;

		endif;

	endif;
	?>

</div>