<?php
/**
 * Content wrappers
 * 
 * Override this template by copying it to yourtheme/global/wrapper-end.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // exit if accessed directly

$template = get_option( 'template' );

switch ( $template ) :

	case 'twentyeleven' :
		$output = '</div></div>';
		break;

	case 'twentytwelve' :
		$output = '</div></div>';
		break;

	case 'twentythirteen' :
		$output = '</div></div>';
		break;

	case 'twentyfourteen' :
		$output = '</div></div></div>';
		get_sidebar( 'content' );
		break;

	case 'twentyfifteen' :
		$output = '</div></main>';
		get_sidebar( 'content' );
		break;

	default :
		$output = '</div></div>';
		break;

endswitch;

echo apply_filters( 'em_content_wrapper_end', $output, $template );