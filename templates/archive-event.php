<?php
/**
 * The template for displaying event archives.
 *
 * Override this template by copying it to yourtheme/archive-event.php or yourtheme/events-maker/archive-event.php
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

				<h1 class="archive-title">
					<?php
					if (em_is_event_archive('day')) :
						printf(__('Event Daily Archives: %s', 'events-maker'), '<span>' . get_the_date() . '</span>');
					elseif ( em_is_event_archive('month')) :
						printf(__('Event Monthly Archives: %s', 'events-maker'), '<span>' . get_the_date(_x('F Y', 'monthly archives date format', 'events-maker')) . '</span>');
					elseif ( em_is_event_archive('year')) :
						printf(__('Event Yearly Archives: %s', 'events-maker'), '<span>' . get_the_date(_x('Y', 'yearly archives date format', 'events-maker')) . '</span>');
					else :
						_e( 'Events', 'events-maker' );
					endif; ?>
				</h1>
				
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