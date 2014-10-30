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

    <?php // location details
    $location = em_get_location();
    $location_details = apply_filters('em_loop_event_location_details', $location->location_meta);
    ?>
    
    <?php
    if (!empty($location) && !is_wp_error($location)) : ?>
    	
    	<?php // location fields
    	foreach ($location_details as $key => $value) :
			
			if (!empty($value) && !in_array($key, array('latitude', 'longitude'))) :
			
		    	switch ($key) :

		    		case 'address' :
						$field['label'] = __('Address', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'zip' :
						$field['label'] = __('Zip Code', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'city' :
						$field['label'] = __('City', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'state' :
						$field['label'] = __('State / Province', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'country' :
						$field['label'] == __('Country', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'image' :
						$field['label'] = __('Image', 'events-maker');
						
						$attr = apply_filters('em_loop_event_location_details_image_attr', array(
							'class'	=> 'attachment-thumbnail photo',
							'alt'   => apply_filters('em_loop_event_location_details_image_title', trim(strip_tags(single_term_title('', false)))),
						));
						$size = apply_filters('em_loop_event_location_details_image_size', 'post-thumbnail');

						$field['content'] = apply_filters('em_loop_event_location_details_image_html', '<br />' . wp_get_attachment_image($location_details['image'], $size, false, $attr));
						break;
						
					default :
						$field['label'] = strtoupper($key);
						$field['content'] = $value;
						break;
						
				endswitch;
				
				$field = apply_filters('em_loop_event_location_details_field', $field, $key);
	
				$html = '<div class="location-' . $key . '">';
					$html .= '<strong>' . $field['label'] . ':</strong> ';
					$html .= $field['content'];
				$html .= '</div>';
					
				echo apply_filters('em_loop_event_location_details_html', $html, $key);
				
			endif;
			
		endforeach;
		?>
    
    <?php endif; ?>

</div>