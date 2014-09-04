<?php
if(!defined('ABSPATH')) exit;

new Events_Maker_Taxonomies($events_maker);

class Events_Maker_Taxonomies
{
	private $options;
	private $category_fields;
	private $location_fields;
	private $organizer_fields;


	public function __construct($events_maker)
	{
		// settings
		$this->options = $events_maker->get_options();

		// actions
		add_action('after_setup_theme', array(&$this, 'load_defaults'));
		add_action('event-category_add_form_fields', array(&$this, 'event_category_add_meta_fields'));
		add_action('event-location_add_form_fields', array(&$this, 'event_location_add_meta_fields'));
		add_action('event-organizer_add_form_fields', array(&$this, 'event_organizer_add_meta_fields'));
		add_action('event-category_edit_form_fields', array(&$this, 'event_category_edit_meta_fields'));
		add_action('event-location_edit_form_fields', array(&$this, 'event_location_edit_meta_fields'));
		add_action('event-organizer_edit_form_fields', array(&$this, 'event_organizer_edit_meta_fields'));
		add_action('edited_event-category', array(&$this, 'event_category_save_meta_fields'));
		add_action('edited_event-location', array(&$this, 'event_location_save_meta_fields'));
		add_action('edited_event-organizer', array(&$this, 'event_organizer_save_meta_fields'));
		add_action('create_event-category', array(&$this, 'event_category_save_meta_fields'));
		add_action('create_event-location', array(&$this, 'event_location_save_meta_fields'));
		add_action('create_event-organizer', array(&$this, 'event_organizer_save_meta_fields'));
	}


	/**
	 * 
	*/
	public function load_defaults()
	{
		$this->category_fields = apply_filters('em_event_category_fields', array(
			'color' => array(
				'label' => __('Category Color', 'events-maker'),
				'default' => '',
				'description' => 'The color of events filed under that category (to be used in Full Calendar display).',
			)
		));

		$this->location_fields = apply_filters('em_event_location_fields', array(
			'google-map' => array(
				'label' => __('Map', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'address' => array(
				'label' => __('Address', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'city' => array(
				'label' => __('City', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'state' => array(
				'label' => __('State / Province', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'zip' => array(
				'label' => __('Zip Code', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'country' => array(
				'label' => __('Country', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'image' => array(
				'label' => __('Image', 'events-maker'),
				'default' => '',
				'description' => '',
			)
		));

		$this->organizer_fields = apply_filters('em_event_organizer_fields', array(
			'contact_name' => array(
				'label' => __('Contact name', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'phone' => array(
				'label' => __('Phone', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'email' => array(
				'label' => __('E-mail', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'website' => array(
				'label' => __('Website', 'events-maker'),
				'default' => '',
				'description' => '',
			),
			'image' => array(
				'label' => __('Image', 'events-maker'),
				'default' => '',
				'description' => ''
			)
		));
	}


	/**
	 * Add fields to category taxonomy
	*/
	public function event_category_add_meta_fields()
	{
		foreach($this->category_fields as $key => $args)
		{
			$html = '<div class="form-field">';

			switch($key)
			{
			    default:
					$html .= '
						<label for="event-'.$key.'">'.$args['label'].'</label>
						<input id="em-color-picker" type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="" size="40"/>';
					break;
			}
			
			if (!empty($args['description']))
				$html .= '<p>' . esc_attr($args['description']) . '</p>';
			
			$html .= '</div>';

			echo $html;
		}
	}


	/**
	 * Add fields to location taxonomy
	*/
	public function event_location_add_meta_fields()
	{	
		foreach($this->location_fields as $key => $args)
		{
			$html = '<div class="form-field">';

			switch($key)
			{
			    case 'google-map':
			        $html .= '
						<label>'.$args['label'].'</label>
						<div id="event-'.$key.'" class="event-minimap">
						</div>
						<input type="hidden" name="term_meta[latitude]" id="event-'.$key.'-latitude" value="0" />
						<input type="hidden" name="term_meta[longitude]" id="event-'.$key.'-longitude" value="0" />';
			        break;

				case 'image':
			        $html .= '
						<div id="em-tax-image-buttons">
							<label>'.$args['label'].'</label>
							<input id="em_upload_image_id" type="hidden" name="term_meta[image]" value="0" />
							<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
							<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" disabled="disabled" />
							<span class="em-spinner"></span>
						</div>
						<div id="em-tax-image-preview">
							<img src="" alt="" style="display: none;" />
						</div>';
			        break;

				default:
					$html .= '
						<label for="event-'.$key.'">'.$args['label'].'</label>
						<input type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="" size="40" class="google-map-input" />';
					break;
			}

			if (!empty($args['description']))
				$html .= '<p>' . esc_attr($args['description']) . '</p>';

			$html .= '</div>';

			echo $html;
		}
	}


	/**
	 * Add fields to organizer taxonomy
	*/
	public function event_organizer_add_meta_fields()
	{
		foreach($this->organizer_fields as $key => $args)
		{
			$html = '<div class="form-field">';

			switch($key)
			{
			    case 'image':
			        $html .= '
						<div id="em-tax-image-buttons">
							<label>'.$args['label'].'</label>
							<input id="em_upload_image_id" type="hidden" name="term_meta[image]" value="0" />
							<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
							<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" disabled="disabled" />
							<span class="em-spinner"></span>
						</div>
						<div id="em-tax-image-preview">
							<img src="" alt="" style="display: none;" />
						</div>';
			        break;

				default:
					$html .= '
						<label for="event-'.$key.'">'.$args['label'].'</label>
						<input type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="" size="40" />';
					break;
			}
			
			if (!empty($args['description']))
				$html .= '<p>' . esc_attr($args['description']) . '</p>';

			$html .= '</div>';

			echo $html;
		}
	}


	/**
	 * Edit fields in category taxonomy
	*/
	public function event_category_edit_meta_fields($term)
	{
		// retrieve the existing value(s) for this meta field, this returns an array
		$term_meta = get_option('event_category_'.$term->term_id);

		foreach($this->category_fields as $key => $args)
		{
			$html = '<tr class="form-field">';

			switch($key)
			{
				case 'color':			
					$html .= '
						<th scope="row" valign="top">
							<label for="event-'.$key.'">'.$args['label'].'</label>
						</th>
						<td>
							<input id="em-color-picker" type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="'.esc_attr($term_meta[$key]).'"/>';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';
					break;
				
				default:
					$html .= '
						<th scope="row" valign="top">
							<label for="event-'.$key.'">'.$args['label'].'</label>
						</th>
						<td>
							<input type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="'.esc_attr($term_meta[$key]).'" />';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';				
					break;
			}

			$html .= '</tr>';

			echo $html;
		}
	}


	/**
	 * Edit fields in location taxonomy
	*/
	public function event_location_edit_meta_fields($term)
	{
		// retrieve the existing value(s) for this meta field, this returns an array
		$term_meta = get_option('event_location_'.$term->term_id);

		// image ID
		$image_id = (int)(isset($term_meta['image']) ? $term_meta['image'] : 0);

		if($image_id !== 0)
			$image = wp_get_attachment_image_src($image_id, 'thumbnail', false);
		else
			$image[0] = '';

		foreach($this->location_fields as $key => $args)
		{
			$html = '<tr class="form-field">';

			switch($key)
			{
			    case 'google-map':
			        $html .= '
						<th scope="row" valign="top">
							<label>'.$args['label'].'</label>
						</th>
						<td>
							<div id="event-google-map">
							</div>
							<input type="hidden" name="term_meta[latitude]" id="event-'.$key.'-latitude" value="'.esc_attr($term_meta['latitude']).'" />
							<input type="hidden" name="term_meta[longitude]" id="event-'.$key.'-longitude" value="'.esc_attr($term_meta['longitude']).'" />';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';		
			        break;

				case 'image':
			        $html .= '
			        	<th scope="row" valign="top">
							<label>'.$args['label'].'</label>
						</th>
							<td>
							<div id="em-tax-image-buttons">
								<input id="em_upload_image_id" type="hidden" name="term_meta[image]" value="'.(int)$image_id.'" />
								<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
								<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" '.disabled($image_id, 0, false).' />
								<span class="em-spinner"></span>
							</div>
							<div id="em-tax-image-preview" class="edit">
								'.($image[0] !== '' ? '<img src="'.$image[0].'" alt="" />' : '<img src="" alt="" style="display: none;" />').'
							</div>';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';
			        break;

				default:
					$html .= '
						<th scope="row" valign="top">
							<label for="event-'.$key.'">'.$args['label'].'</label>
						</th>
						<td>
							<input type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="'.esc_attr($term_meta[$key]).'" class="google-map-input" />';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';
					break;
			}

			$html .= '</tr>';

			echo $html;
		}
	}


	/**
	 * Edit fields in organizer taxonomy
	*/
	public function event_organizer_edit_meta_fields($term)
	{
		// retrieve the existing value(s) for this meta field, this returns an array
		$term_meta = get_option('event_organizer_'.$term->term_id);

		// image ID
		$image_id = (int)(isset($term_meta['image']) ? $term_meta['image'] : 0);

		if($image_id !== 0)
			$image = wp_get_attachment_image_src($image_id, 'thumbnail', false);
		else
			$image[0] = '';

		foreach($this->organizer_fields as $key => $name)
		{
			$html = '<tr class="form-field">';

			switch($key)
			{
			    case 'image':
			        $html .= '
			        	<th scope="row" valign="top">
							<label>'.$name.'</label>
						</th>
							<td>
							<div id="em-tax-image-buttons">
								<input id="em_upload_image_id" type="hidden" name="term_meta[image]" value="'.(int)$image_id.'" />
								<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
								<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" '.disabled($image_id, 0, false).' />
								<span class="em-spinner"></span>
							</div>
							<div id="em-tax-image-preview" class="edit">
								'.($image[0] !== '' ? '<img src="'.$image[0].'" alt="" />' : '<img src="" alt="" style="display: none;" />').'
							</div>';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';
			        break;

				default:
					$html .= '
						<th scope="row" valign="top">
							<label for="event-'.$key.'">'.$name.'</label>
						</th>
						<td>
							<input type="text" name="term_meta['.$key.']" id="event-'.$key.'" value="'.esc_attr($term_meta[$key]).'" />';
					
					if (!empty($args['description']))
						$html .= 
							'<br /><span class="description">' . esc_attr($args['description']) . '</span>';
					
					$html .= 
						'</td>';
					break;
			}

			$html .= '</tr>';

			echo $html;
		}
	}


	/**
	 * Save fields in category taxonomy
	*/
	public function event_category_save_meta_fields($term_id)
	{
		if(isset($_POST['term_meta']) && is_array($_POST['term_meta']))
		{
			$term_meta = array();

			foreach($this->category_fields as $key => $args)
			{
				switch($key)
				{
					default:
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = sanitize_text_field($_POST['term_meta'][$key]);
						break;
				}
			}

			update_option('event_category_'.$term_id, $term_meta);
		}
	}


	/**
	 * Save fields in location taxonomy
	*/
	public function event_location_save_meta_fields($term_id)
	{
		if(isset($_POST['term_meta']) && is_array($_POST['term_meta']))
		{
			$term_meta = array();

			foreach($this->location_fields as $key => $args)
			{
				switch($key)
				{
			    	case 'google-map':
						if(isset($_POST['term_meta']['latitude']))
							$term_meta['latitude'] = sanitize_text_field($_POST['term_meta']['latitude']);
						if(isset($_POST['term_meta']['longitude']))
							$term_meta['longitude'] = sanitize_text_field($_POST['term_meta']['longitude']);
						break;

					case 'image':
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = (int)$_POST['term_meta'][$key];
						break;

					default:
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = sanitize_text_field($_POST['term_meta'][$key]);
						break;
				}
			}

			update_option('event_location_'.$term_id, $term_meta);
		}
	}


	/**
	 * Save fields in organizer taxonomy
	*/
	public function event_organizer_save_meta_fields($term_id)
	{
		if(isset($_POST['term_meta']) && is_array($_POST['term_meta']))
		{
			$term_meta = array();

			foreach($this->organizer_fields as $key => $args)
			{
				switch($key)
				{
			    	case 'image':
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = (int)$_POST['term_meta'][$key];
						break;
						
					case 'email':
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = sanitize_email($_POST['term_meta'][$key]);
						break;
						
					case 'website':
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = esc_url($_POST['term_meta'][$key]);
						break;

					default:
						if(isset($_POST['term_meta'][$key]))
							$term_meta[$key] = sanitize_text_field($_POST['term_meta'][$key]);
						break;
				}
			}

			update_option('event_organizer_'.$term_id, $term_meta);
		}
	}
}
?>