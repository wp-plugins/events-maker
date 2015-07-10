<?php
/**
 * The template for displaying event search form
 *
 * Override this template by copying it to yourtheme/searchform-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.6.5
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

// if in a shortcode, extract args
if ( $args && is_array( $args ) ) :
	extract( $args );
endif;

// extra searchform classes
$classes = apply_filters( 'em_searchform_event_classes', array( 'search-form' ) );
?>

<form role="search" class="em-search-form <?php echo implode( ' ', $classes ); ?>" action="<?php echo esc_url( $link ); ?>" method="get">
	
	<?php
	/**
	 * em_before_searchform_event hook
	 */
	do_action( 'em_before_searchform_event' );
	?>
	
	<?php // searchphrase input field
	if ( $args['show_string_input'] ) :
		
		$value = ! empty( $_GET['s'] ) ? esc_attr( urlencode( $_GET['s'] ) ) : '';
		?>
		<label><input type="search" value="<?php echo $value; ?>" name="s" class="em-search-string search-field" placeholder="<?php echo esc_html( $args['string_input_placeholder'] ); ?>" /></label>
		
	<?php endif; ?>

	<?php // date input fields
	if ( $args['show_date_input'] ) :
		
		$value_start = ! empty( $_GET['start_date'] ) ? esc_attr( urlencode( $_GET['start_date'] ) ) : '';
		$value_end = ! empty( $_GET['end_date'] ) ? esc_attr( urlencode( $_GET['end_date'] ) ) : '';
		?>
		<label><input id="em-search-start-date" type="search" value="<?php echo $value_start; ?>" name="start_date" class="search-field" placeholder="<?php echo esc_html( $args['start_date_placeholder'] ); ?>" /></label>
		<label><input id="em-search-end-date" type="search" value="<?php echo $value_end; ?>" name="end_date" class="search-field" placeholder="<?php echo esc_html( $args['end_date_placeholder'] ); ?>" /></label>
		
	<?php endif; ?>

	<?php // categories dropdown field
	if ( $args['show_event_categories'] ) :
		
		$terms = get_terms( 'event-category', array( 'fields' => 'id=>name' ) );
		$value = ! empty( $_GET['tax_event-category'] ) ? absint( $_GET['tax_event-category'] ) : 0;

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			?>
			<select class="em-search-categories postform" name="tax_event-category">
				<option value="0"><?php _e( 'All Categories', 'events-maker' ); ?></option>
				<?php foreach( $terms as $term_id => $name ) : ?>
					<option value="<?php echo absint( $term_id ); ?>" <?php selected( $value, absint( $term_id ), true ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
			
	<?php endif; ?>

	<?php // locations dropdown field
	if ( $args['show_event_locations'] ) :
		
		$terms = get_terms( 'event-location', array( 'fields' => 'id=>name' ) );
		$value = ! empty( $_GET['tax_event-location'] ) ? absint( $_GET['tax_event-location'] ) : 0;

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			?>
			<select class="em-search-locations postform" name="tax_event-location">
				<option value="0"><?php _e( 'All Locations', 'events-maker' ); ?></option>
				<?php foreach( $terms as $term_id => $name ) : ?>
					<option value="<?php echo absint( $term_id ); ?>" <?php selected( $value, absint( $term_id ), true ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
			
	<?php endif; ?>

	<?php // organizers dropdown field
	if ( $args['show_event_organizers'] && taxonomy_exists( 'event-organizer' ) ) :
		
		$terms = get_terms( 'event-organizer', array( 'fields' => 'id=>name' ) );
		$value = ! empty( $_GET['tax_event-organizer'] ) ? absint( $_GET['tax_event-organizer'] ) : 0;

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			?>
			<select class="em-search-organizers postform" name="tax_event-organizer">
				<option value="0"><?php _e( 'All Organizers', 'events-maker' ); ?></option>
				<?php foreach( $terms as $term_id => $name ) : ?>
					<option value="<?php echo absint( $term_id ); ?>" <?php selected( $value, absint( $term_id ), true ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
			
	<?php endif; ?>

	<?php // tags dropdown field
	if ( $args['show_event_tags'] ) :
		
		$terms = get_terms( 'event-tag', array( 'fields' => 'id=>name' ) );
		$value = ! empty( $_GET['tax_event-tag'] ) ? absint( $_GET['tax_event-tag'] ) : 0;

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			?>
			<select class="em-search-tags postform" name="tax_event-tag">
				<option value="0"><?php _e( 'All Tags', 'events-maker' ); ?></option>
				<?php foreach( $terms as $term_id => $name ) : ?>
					<option value="<?php echo absint( $term_id ); ?>" <?php selected( $value, absint( $term_id ), true ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
			
	<?php endif; ?>
		
	<?php // submit
	$submit_button_text = apply_filters( 'em_searchform_event_submit_button_text', __( 'Search', 'events-maker' ) );

	echo apply_filters( 'em_searchform_event_submit_button', '<button class="em-search-submit" type="submit" value="' . $submit_button_text . '">' . $submit_button_text . '</button>' );
	?>

	<?php
	/**
	 * em_after_searchform_event hook
	 */
	do_action( 'em_after_searchform_event' );
	?>

</form>