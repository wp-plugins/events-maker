<?php
/**
 * Event content in single event
 * 
 * Override this template by copying it to yourtheme/single-event/content.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.3.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $post;
?>

<div class="entry-content description">

	<?php the_content(); ?>

</div>