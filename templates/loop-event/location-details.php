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
    ?>
    
    <?php
    if (!empty($location) && !is_wp_error($location)) : ?>
    	
    	<?php // location fields
    	$location_fields = em_get_event_location_fields();
    	$location_details = apply_filters('em_loop_event_location_details', $location->location_meta);
		
    	foreach ($location_fields as $key => $field) :

			// field value
			$field['value'] = $location_details[$key];
			
			// field filter hook
			$field = apply_filters('em_loop_event_location_details_field', $field, $key);
			
			if (!empty($field['value']) && !in_array($field['type'], array('google_map', 'image'))) :
				
				switch ($field['type'])
				{
					case 'image' :
						$attr = apply_filters('em_loop_event_location_details_image_attr', array(
							'class'	=> 'attachment-thumbnail photo',
							'alt'   => apply_filters('em_loop_event_location_details_image_title', trim(strip_tags(single_term_title('', false)))),
						));
						$size = apply_filters('em_loop_event_location_details_image_size', 'post-thumbnail');
					
						$content = apply_filters('em_loop_event_location_details_image_html', '<br />' . wp_get_attachment_image((int)$field['value'], $size, false, $attr));
						break;
						
					default :
						$content = wp_kses_post($field['value']);
				}

				$html = '<div class="location-' . $key . '">';
					$html .= '<strong>' . $field['label'] . ':</strong> ';
					$html .= $content;
				$html .= '</div>';
					
				echo apply_filters('em_loop_event_location_details_html', $html, $key);
				
			endif;
			
		endforeach;
    
	endif;
	?>

</div>