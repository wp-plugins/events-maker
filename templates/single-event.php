<?php
/**
 * The template for displaying all single events.
 *
 * Override this template by copying it to yourtheme/single-event.php or yourtheme/events-maker/single-event.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header(); ?>

	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">

			<?php // Start the Loop
			while (have_posts()) : the_post(); ?>

				<?php em_get_template_part('content', 'single-event'); ?>

				<?php // If comments are open or we have at least one comment, load up the comment template.
				if (comments_open() || get_comments_number()) {
					comments_template();
				} ?>

			<?php endwhile; ?>

		</div>

	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>