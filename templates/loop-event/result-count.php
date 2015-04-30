<?php
/**
 * Result Count
 *
 * Override this template by copying it to yourtheme/loop-event/result-count.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.5.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $wp_query;

if ( $wp_query->found_posts == 0 )
	return;
?>
<div class="events-maker-result-count">

	<span class="result-string"><?php printf( _n( '1 event found.', '%d events found.', $wp_query->found_posts, 'events-maker' ), $wp_query->found_posts ); ?></span>

</div>