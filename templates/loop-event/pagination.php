<?php
/**
 * Pagination - Show numbered pagination for catalog pages.
 * 
 * Override this template by copying it to yourtheme/loop-event/pagination.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

global $wp_query;

if ( $wp_query->max_num_pages <= 1 )
	return;
?>

<nav class="navigation paging-navigation pagination" role="navigation">

	<div class="loop-pagination nav-links">

		<?php
		$big = 999999999; // need an unlikely integer
		$args = array();

		$defaults = array(
			'base'			 => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'		 => '?paged=%#%',
			'total'			 => $wp_query->max_num_pages,
			'current'		 => max( 1, get_query_var( 'paged' ) ),
			'show_all'		 => false,
			'end_size'		 => 1,
			'mid_size'		 => 2,
			'prev_next'		 => true,
			'prev_text'		 => __( '&laquo; Previous', 'events-maker' ),
			'next_text'		 => __( 'Next &raquo;', 'events-maker' ),
			'type'			 => 'plain',
			'add_args'		 => False,
			'add_fragment'	 => ''
		);

		$args = apply_filters( 'em_paginate_links_args', wp_parse_args( $defaults, $args ) );

		echo paginate_links( $args );
		?>

	</div>

</nav>