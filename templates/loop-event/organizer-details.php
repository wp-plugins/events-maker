<?php
/**
 * Event organizer details
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

    <?php // organizer details
    $organizer = em_get_organizer();
    ?>
    
    <?php
    if (!empty($organizer) && !is_wp_error($organizer)) : ?>
    
    	<?php // organizer fields
    	$organizer_fields = em_get_event_organizer_fields();
		$organizer_details = apply_filters('em_loop_event_organizer_details', (isset($organizer->organizer_meta) ? $organizer->organizer_meta : ''));
		
		if (!empty($organizer_fields) && !empty($organizer_details)) :
			
	    	foreach ($organizer_fields as $key => $field) :
				
				// field value
				$field['value'] = $organizer_details[$key];
				
				// field filter hook
				$field = apply_filters('em_loop_event_organizer_details_field', $field, $key);
				
				if (!empty($field['value'])) :
					
					switch ($field['type'])
					{
						case 'image' :
							$attr = apply_filters('em_loop_event_organizer_details_image_attr', array(
								'class'	=> 'attachment-thumbnail photo',
								'alt'   => apply_filters('em_loop_event_organizer_details_image_title', trim(strip_tags(single_term_title('', false)))),
							));
							$size = apply_filters('em_loop_event_organizer_details_image_size', 'post-thumbnail');
						
							$content = apply_filters('em_loop_event_organizer_details_image_html', '<br />' . wp_get_attachment_image((int)$field['value'], $size, false, $attr));
							break;
							
						default :
							$content = wp_kses_post($field['value']);
					}
		
					$html = '<div class="organizer-' . $key . '">';
						$html .= '<strong>' . $field['label'] . ':</strong> ';
						$html .= $content;
					$html .= '</div>';
						
					echo apply_filters('em_loop_event_organizer_details_html', $html, $key);
					
				endif;
				
			endforeach;
		
		endif;
    
	endif;
	?>

</div>