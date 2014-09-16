<?php
/**
 * Event location details
 * 
 * Override this template by copying it to yourtheme/loop-event/organizer-details.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div class="archive-meta entry-meta">

    <?php
    $organizer = em_get_organizer();
    $organizer_details = $organizer->organizer_meta;
    ?>
    
    <?php
    if (!empty($organizer) && !is_wp_error($organizer)) : ?>
    
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
        	
        	<div class="archive-thumbnail organizer-image">
        		
        		<strong><?php echo __('Image', 'events-maker'); ?>:</strong><br />
        		<?php $image_thb = wp_get_attachment_image_src($organizer_details['image'], 'post-thumbnail'); ?>
        		<img src="<?php echo $image_thb[0]; ?>" class="attachment-thumbnail photo" title="<?php echo single_term_title('', false); ?>" alt="<?php echo single_term_title('', false); ?>" />
        	
        	</div>
        
        <?php endif; ?>
    
    <?php endif; ?>

</div>