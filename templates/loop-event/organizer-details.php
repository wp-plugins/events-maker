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
    $organizer_details = apply_filters('em_loop_event_organizer_details', $organizer->organizer_meta);
    ?>
    
    <?php
    if (!empty($organizer) && !is_wp_error($organizer)) : ?>
    
    	<?php // organizer fields
    	foreach ($organizer_details as $key => $value) :
			
			if (!empty($value)) :
			
		    	switch ($key) :

		    		case 'contact_name' :
						$field['label'] = __('Contact name', 'events-maker');
						$field['content'] = '<span class="fn">' . $value . '</span>';
						break;
						
					case 'phone' :
						$field['label'] = __('Phone', 'events-maker');
						$field['content'] = '<span class="tel">' . $value . '</span>';
						break;
						
					case 'email' :
						$field['label'] = __('Email', 'events-maker');
						$field['content'] = '<span class="email">' . $value . '</span>';
						break;
						
					case 'website' :
						$field['label'] = __('Website', 'events-maker');
						$field['content'] = '<span class="fn"><a href="' . esc_url($value) . '" target="_blank" rel="nofollow">' . $value . '</a></span>';
						break;
						
					case 'country' :
						$field['label'] = __('Country', 'events-maker');
						$field['content'] = $value;
						break;
						
					case 'image' :
						$field['label'] = __('Image', 'events-maker');
						
						$attr = apply_filters('em_loop_event_organizer_details_image_attr', array(
							'class'	=> 'attachment-thumbnail photo',
							'alt'   => apply_filters('em_loop_event_organizer_details_image_title', trim(strip_tags(single_term_title('', false)))),
						));
						$size = apply_filters('em_loop_event_organizer_details_image_size', 'post-thumbnail');

						$field['content'] = apply_filters('em_loop_event_organizer_details_image_html', '<br />' . wp_get_attachment_image($organizer_details['image'], $size, false, $attr));
						break;
						
					default :
						$field['label'] = strtoupper($key);
						$field['content'] = $value;
						break;
						
				endswitch;
				
				$field = apply_filters('em_loop_event_organizer_details_field', $field, $key);
	
				$html = '<div class="organizer-' . $key . '">';
					$html .= '<strong>' . $field['label'] . ':</strong> ';
					$html .= $field['content'];
				$html .= '</div>';
					
				echo apply_filters('em_loop_event_organizer_details_html', $html, $key);
				
			endif;
			
		endforeach;
		?>
    
    <?php endif; ?>

</div>