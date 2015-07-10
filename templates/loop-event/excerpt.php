<?php
/**
 * Event excerpt in loop
 * 
 * Override this template by copying it to yourtheme/loop-event/excerpt.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post;
?>

<div class="entry-content description">

	<?php echo apply_filters( 'em_loop_event_excerpt', get_the_excerpt() ); ?>

</div>