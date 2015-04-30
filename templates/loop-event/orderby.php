<?php
/**
 * Events ordering options
 * 
 * Override this template by copying it to yourtheme/loop-event/orderby.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.5.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $wp_query;

if ( $wp_query->found_posts < 2 )
	return;

// current url
if ( empty( $link ) ) :
	global $wp;

	$link = esc_url( add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
endif;

// current orderby value
if ( isset( $_GET['orderby'] ) ) :
	$orderby = esc_attr( $_GET['orderby'] );
else :
	$orderby = em_get_default_orderby();
endif;
?>

<form class="events-maker-orderby" action="<?php echo esc_url( $link ); ?>" method="get">

	<select name="orderby" id="event-orderby" class="orderby">

		<?php
		$args = apply_filters( 'em_events_orderby', array(
			'event_start_date-asc'	 => __( 'Sort by start date: ascending', 'events-maker' ),
			'event_start_date-desc'	 => __( 'Sort by start date: descending', 'events-maker' ),
			'event_end_date-asc'	 => __( 'Sort by end date: ascending', 'events-maker' ),
			'event_end_date-desc'	 => __( 'Sort by end date: descending', 'events-maker' ),
			'title-asc'				 => __( 'Sort by title: ascending', 'events-maker' ),
			'title-desc'			 => __( 'Sort by title: descending', 'events-maker' )
		) );

		foreach ( $args as $id => $name )
			echo '<option value="' . esc_attr( $id ) . '" ' . selected( $orderby, $id, false ) . '>' . esc_attr( $name ) . '</option>';
		?>

	</select>

	<?php
	// keep query string vars intact
	foreach ( $_GET as $key => $val ) :

		if ( 'orderby' === $key || 'submit' === $key )
			continue;

		if ( is_array( $val ) ) :

			foreach ( $val as $_val ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( $key ) . '[]'; ?>" value="<?php echo esc_attr( $_val ); ?>" />
				<?php
			endforeach;

		else :
			?>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
		<?php
		endif;

	endforeach;
	?>

</form>