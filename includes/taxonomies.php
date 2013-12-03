<?php
if(!defined('ABSPATH')) exit; //exit if accessed directly

new Events_Maker_Taxonomies();

class Events_Maker_Taxonomies
{
	private $options = array();
	private $locations = array('latitude', 'longitude', 'address', 'city', 'state', 'zip', 'country');
	private $organizers = array('contact_name', 'phone', 'email', 'website', 'image');


	public function __construct()
	{
		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general'))
		);

		//actions
		add_action('event-location_add_form_fields', array(&$this, 'event_location_add_meta_fields'));
		add_action('event-organizer_add_form_fields', array(&$this, 'event_organizer_add_meta_fields'));
		add_action('event-location_edit_form_fields', array(&$this, 'event_location_edit_meta_fields'));
		add_action('event-organizer_edit_form_fields', array(&$this, 'event_organizer_edit_meta_fields'));
		add_action('edited_event-location', array(&$this, 'event_location_save_meta_fields'));
		add_action('edited_event-organizer', array(&$this, 'event_organizer_save_meta_fields'));
		add_action('create_event-location', array(&$this, 'event_location_save_meta_fields'));
		add_action('create_event-organizer', array(&$this, 'event_organizer_save_meta_fields'));
	}


	/**
	 * Add fields to location taxonomy
	*/
	public function event_location_add_meta_fields()
	{
		echo '
		<div class="form-field">
			<label>'.__('Map', 'events-maker').'</label>
			<div id="event-google-map" class="event-minimap">
			</div>
			<input type="hidden" name="event_location[latitude]" id="event-location-latitude" value="0" />
			<input type="hidden" name="event_location[longitude]" id="event-location-longitude" value="0" />
		</div>
		<div class="form-field">
			<label for="event-location-address">'.__('Address', 'events-maker').'</label>
			<input type="text" name="event_location[address]" id="event-location-address" class="em-gm-input" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-location-city">'.__('City', 'events-maker').'</label>
			<input type="text" name="event_location[city]" id="event-location-city" class="em-gm-input" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-location-state">'.__('State / Province', 'events-maker').'</label>
			<input type="text" name="event_location[state]" id="event-location-state" class="em-gm-input" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-location-zip">'.__('Zip Code', 'events-maker').'</label>
			<input type="text" name="event_location[zip]" id="event-location-zip" class="em-gm-input" value="" size="40" />
      	</div>
     	<div class="form-field">
            <label for="event-location-country">'.__('Country', 'events-maker').'</label>
			<input type="text" name="event_location[country]" id="event-location-country" class="em-gm-input" value="" size="40" />
		</div>';
	}


	/**
	 * Add fields to organizer taxonomy
	*/
	public function event_organizer_add_meta_fields()
	{
		echo '
		<div class="form-field">
			<label for="event-organizer-contact-name">'.__('Contact name', 'events-maker').'</label>
			<input type="text" name="event_organizer[contact_name]" id="event-organizer-contact-name" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-organizer-phone">'.__('Phone', 'events-maker').'</label>
			<input type="text" name="event_organizer[phone]" id="event-organizer-phone" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-organizer-email">'.__('E-mail', 'events-maker').'</label>
			<input type="text" name="event_organizer[email]" id="event-organizer-email" value="" size="40" />
      	</div>
        <div class="form-field">
            <label for="event-organizer-website">'.__('Website', 'events-maker').'</label>
			<input type="text" name="event_organizer[website]" id="event-organizer-website" value="" size="40" />
      	</div>
		<div class="form-field">
			<div id="em-organizer-image-buttons">
				<label>'.__('Image', 'events-maker').'</label>
				<input id="em_upload_image_id" type="hidden" name="event_organizer[image]" value="0" />
				<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
				<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" disabled="disabled" />
				<span class="em-spinner"></span>
			</div>
			<div id="em-organizer-image-preview">
				<img src="" alt="" style="display: none;" />
			</div>
      	</div>';
	}


	/**
	 * Edit fields in location taxonomy
	*/
	public function event_location_edit_meta_fields($term)
	{
		//retrieve the existing value(s) for this meta field, this returns an array
		$term_meta = get_option('event_location_'.$term->term_id);

		echo '
		<tr class="form-field">
			<th scope="row" valign="top">
				<label>'.__('Map', 'events-maker').'</label>
			</th>
			<td>
				<div id="event-google-map">
				</div>
				<input type="hidden" name="event_location[latitude]" id="event-location-latitude" value="'.esc_attr($term_meta['latitude']).'" />
				<input type="hidden" name="event_location[longitude]" id="event-location-longitude" value="'.esc_attr($term_meta['longitude']).'" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-location-address">'.__('Address', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_location[address]" id="event-location-address" value="'.esc_attr($term_meta['address']).'" class="em-gm-input" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-location-city">'.__('City', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_location[city]" id="event-location-city" value="'.esc_attr($term_meta['city']).'" class="em-gm-input" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-location-state">'.__('State / Province', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_location[state]" id="event-location-state" value="'.esc_attr($term_meta['state']).'" class="em-gm-input" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-location-zip">'.__('Zip Code', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_location[zip]" id="event-location-zip" value="'.esc_attr($term_meta['zip']).'" class="em-gm-input" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-location-country">'.__('Country', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_location[country]" id="event-location-country" value="'.esc_attr($term_meta['country']).'" class="em-gm-input" />
			</td>
		</tr>';
	}


	/**
	 * Edit fields in organizer taxonomy
	*/
	public function event_organizer_edit_meta_fields($term)
	{
		//retrieve the existing value(s) for this meta field, this returns an array
		$term_meta = get_option('event_organizer_'.$term->term_id);

		//image ID
		$image_id = (int)(isset($term_meta['image']) ? $term_meta['image'] : 0);

		if($image_id !== 0)
			$image = wp_get_attachment_image_src($image_id, 'thumbnail', FALSE);
		else
			$image[0] = '';

		echo '
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-organizer-contact-name">'.__('Contact name', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_organizer[contact_name]" id="event-organizer-contact-name" value="'.esc_attr($term_meta['contact_name']).'" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-organizer-phone">'.__('Phone', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_organizer[phone]" id="event-organizer-phone" value="'.esc_attr($term_meta['phone']).'" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-organizer-email">'.__('E-mail', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_organizer[email]" id="event-organizer-email" value="'.esc_attr($term_meta['email']).'" />
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top">
				<label for="event-organizer-website">'.__('Website', 'events-maker').'</label>
			</th>
			<td>
				<input type="text" name="event_organizer[website]" id="event-organizer-website" value="'.esc_url($term_meta['website']).'" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label>'.__('Image', 'events-maker').'</label>
			</th>
			<td>
				<div id="em-organizer-image-buttons">
					<input id="em_upload_image_id" type="hidden" name="event_organizer[image]" value="'.$image_id.'" />
					<input id="em_upload_image_button" type="button" class="button button-secondary" value="'.__('Select image', 'events-maker').'" />
					<input id="em_turn_off_image_button" type="button" class="button button-secondary" value="'.__('Remove image', 'events-maker').'" '.disabled($image_id, 0, FALSE).' />
					<span class="em-spinner"></span>
				</div>
				<div id="em-organizer-image-preview" class="edit">
					'.($image[0] !== '' ? '<img src="'.$image[0].'" alt="" />' : '<img src="" alt="" style="display: none;" />').'
				</div>
			</td>
		</tr>';
	}


	/**
	 * Save fields in location taxonomy
	*/
	public function event_location_save_meta_fields($term_id)
	{
		if(isset($_POST['event_location']) && is_array($_POST['event_location']))
		{
			$term_meta = array();

			foreach($this->locations as $key)
			{
				if(isset($_POST['event_location'][$key]))
					$term_meta[$key] = sanitize_text_field($_POST['event_location'][$key]);
			}

			update_option('event_location_'.$term_id, $term_meta);
		}
	}


	/**
	 * Save fields in organizer taxonomy
	*/
	public function event_organizer_save_meta_fields($term_id)
	{
		if(isset($_POST['event_organizer']) && is_array($_POST['event_organizer']))
		{
			$term_meta = array();

			foreach($this->organizers as $key)
			{
				if(isset($_POST['event_organizer'][$key]))
				{
					if($key !== 'image')
						$term_meta[$key] = sanitize_text_field($_POST['event_organizer'][$key]);
					else
						$term_meta[$key] = (int)$_POST['event_organizer'][$key];
				}
			}

			update_option('event_organizer_'.$term_id, $term_meta);
		}
	}
}
?>