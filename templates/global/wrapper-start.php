<?php
/**
 * Content wrappers
 * 
 * Override this template by copying it to yourtheme/global/wrapper-start.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$template = get_option('template');

switch($template) {
	case 'twentyeleven' :
		$output = '<div id="primary"><div id="content" role="main">';
		break;
	case 'twentytwelve' :
		$output = '<div id="primary" class="site-content"><div id="content" role="main">';
		break;
	case 'twentythirteen' :
		$output = '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		$output = '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tf-fix">';
		break;
	case 'twentyfifteen' :
		$output = '<div id="primary" class="content-area"><main id="main" role="main" class="site-main twentyfifteen">';
		break;
	default :
		$output = '<div id="container"><div id="content" role="main">';
		break;
}

echo apply_filters('em_content_wrapper_start', $output, $template);