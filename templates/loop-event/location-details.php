<?php
/**
 * Event location details
 * 
 * Override this template by copying it to yourtheme/loop-event/location-details.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div class="archive-meta entry-meta">

    <?php
    $location = em_get_location();
    $location_details = $location->location_meta;
    ?>
    
    <?php
    if (!empty($location) && !is_wp_error($location)) : ?>
    
	    <?php if (!empty($location_details['address'])) : ?>	    	
	    	<div class="location-address"><strong><?php echo __('Address', 'events-maker'); ?>:</strong> <?php echo $location_details['address']; ?></div>
	    <?php endif; ?>
	    
	    <?php if (!empty($location_details['zip'])) : ?>    	
	    	<div class="location-zip"><strong><?php echo __('Zip Code', 'events-maker'); ?>:</strong> <?php echo $location_details['zip']; ?></div>
	    <?php endif; ?>
	    
	    <?php if (!empty($location_details['city'])) : ?>   	
	    	<div class="location-city"><strong><?php echo __('City', 'events-maker'); ?>:</strong> <?php echo $location_details['city']; ?></div>
	    <?php endif; ?>
	    
	    <?php if (!empty($location_details['state'])) : ?>	
	    	<div class="location-state"><strong><?php echo __('State / Province', 'events-maker'); ?>:</strong> <?php echo $location_details['state']; ?></div>
	    <?php endif; ?>
	         
	    <?php if (!empty($location_details['country'])) : ?>	
	    	<div class="location-country"><strong><?php echo __('Country', 'events-maker'); ?>:</strong> <?php echo $location_details['country']; ?></div>
	    <?php endif; ?>
	    	
	    <?php if (!empty($location_details['image'])) : ?>
        	
        	<div class="archive-thumbnail location-image">
        		
        		<strong><?php echo __('Image', 'events-maker'); ?>:</strong><br />
        		<?php $image_thb = wp_get_attachment_image_src($location_details['image'], 'post-thumbnail'); ?>
        		<img src="<?php echo $image_thb[0]; ?>" class="attachment-thumbnail photo" title="<?php echo single_term_title('', false); ?>" alt="<?php echo single_term_title('', false); ?>" />
        	
        	</div>
        
        <?php endif; ?>
    
    <?php endif; ?>

</div>