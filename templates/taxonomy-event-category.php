<?php
/**
 * The template for displaying event category archives.
 *
 * Override this template by copying it to yourtheme/taxonomy-event-category.php or yourtheme/events-maker/taxonomy-event-category.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.1.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header(); ?>

	<section id="primary" class="content-area">

		<div id="content" class="site-content" role="main">

		<?php if (have_posts()) : ?>

			<header class="archive-header">

				<h1 class="archive-title"><?php printf(__( 'Events Category: %s', 'events-maker'), single_term_title('', false)); ?></h1>

				<?php // Show an optional term description.
				$term_description = term_description();
				if (!empty($term_description)) :
					printf('<div class="archive-description taxonomy-description">%s</div>', $term_description);
				endif; ?>

			</header>

			<?php // Start the Loop
			while (have_posts()) : the_post(); ?>
				
				<?php em_get_template_part('content', 'event'); ?>

			<?php endwhile; ?>

			<?php // Pagination
            if ($wp_query->max_num_pages > 1) : ?>

                <nav id="nav-below" class="navigation paging-navigation" role="navigation">
                	
                	<div class="pagination loop-pagination">

                    	<?php em_paginate_links(); ?>
                    	
                   	</div>

                </nav>

            <?php endif; ?>

		<?php else : ?>

            <?php em_get_template_part('content', 'event-none'); ?>

        <?php endif; ?>

		</div>

	</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>