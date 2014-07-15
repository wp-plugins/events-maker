<?php
/**
 * The template for displaying event organizer archives.
 *
 * Override this template by copying it to yourtheme/taxonomy-event-organizer.php or yourtheme/events-maker/taxonomy-event-organizer.php
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

			<header class="archive-header vcard">

				<h1 class="archive-title org"><?php printf(__('Events Organizer: %s', 'events-maker'), single_term_title('', false)); ?></h1>
				
				<div class="archive-meta entry-meta">

	                <?php // Display additional organizer info ?>
	                <?php $organizer = em_get_organizer(); ?>
	                <?php $organizer_details = $organizer->organizer_meta; ?>
	                <?php if (!empty($organizer_details['contact_name'])) : ?>
	                	<div class="organizer-contact-name"><strong><?php echo __('Contact name', 'events-maker'); ?>:</strong> <span class="fn"><?php echo $organizer_details['contact_name']; ?></span></div>
	                <?php endif; ?>
	                <?php if (!empty($organizer_details['phone'])) : ?>
	                	<div class="organizer-phone"><strong><?php echo __('Phone', 'events-maker'); ?>:</strong> <span class="tel"><?php echo $organizer_details['phone']; ?></span></div>
	                <?php endif; ?>
	                <?php if (!empty($organizer_details['email'])) : ?>
	                	<div class="organizer-email"><strong><?php echo __('Email', 'events-maker'); ?>:</strong> <span class="email"><?php echo $organizer_details['email']; ?></span></div>
	                <?php endif; ?>
	                <?php if (!empty($organizer_details['website'])) : ?>
	                	<div class="organizer-website"><strong><?php echo __('Website', 'events-maker'); ?>:</strong> <span class="fn"><a href="<?php echo $organizer_details['website']; ?>" target="_blank" rel="nofollow"><?php echo $organizer_details['website']; ?></a></span></div>
	                <?php endif; ?>
	                <?php if (!empty($organizer_details['image'])) : ?>
	                	<div class="organizer-image"><strong><?php echo __('Image', 'events-maker'); ?>:</strong><br />
	                		<?php $image_thb = wp_get_attachment_image_src($organizer_details['image'], 'thumbnail'); ?>
	                		<img src="<?php echo $image_thb[0]; ?>" class="attachment-thumbnail photo" title="<?php echo single_term_title('', false); ?>" alt="<?php echo single_term_title('', false); ?>" />
	                	</div>
	                <?php endif; ?>

                </div>

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