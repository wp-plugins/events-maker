<?php
/**
 * The template for displaying event tag archives.
 *
 * Override this template by copying it to yourtheme/taxonomy-event-tag.php or yourtheme/events-maker/taxonomy-event-tag.php
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

				<h1 class="archive-title"><?php printf(__('Events Tag: %s', 'events-maker'), single_term_title('', false)); ?></h1>

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

            <article id="post-0" class="post no-results not-found">
	
			    <header class="entry-header">
			        <h1 class="entry-title"><?php _e('No Events Found', 'events-maker'); ?></h1>
			    </header>
			
			    <div class="entry-content">
			        <p><?php _e('Apologies, but no events were found.', 'events-maker'); ?></p>
			    </div>
			
			</article>

        <?php endif; ?>

		</div>

	</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>