<?php
if(!defined('ABSPATH')) exit;

new Events_Maker_Templates($events_maker);

class Events_Maker_Templates
{
	private $options = array();


	public function __construct($events_maker)
	{
		//settings
		$this->options = $events_maker->get_options();

		//filters
		add_filter('template_include', array(&$this, 'template_include'));
		add_filter('post_class', array(&$this, 'remove_hentry'));
	}
	
	
	// Locate and include template files
	public function template_include($template)
	{
		if ($this->options['templates']['default_templates'] === true)
		{
			if(is_post_type_archive('event'))
				$new_template = em_locate_template('archive-event.php');
	
			if(is_tax('event-category'))
				$new_template = em_locate_template('taxonomy-event-category.php');
			
			if(is_tax('event-location'))
				$new_template = em_locate_template('taxonomy-event-location.php');
			
			if(is_tax('event-organizer'))
				$new_template = em_locate_template('taxonomy-event-organizer.php');
			
			if(is_tax('event-tag'))
				$new_template = em_locate_template('taxonomy-event-tag.php');
	
			if(is_singular('event'))
				$new_template = em_locate_template('single-event.php');
		}
		
		return apply_filters('em_template_include', (!empty($new_template) ? $new_template : $template));
	}
	
	
	// Remove hentry from event post classes
	public function remove_hentry($classes)
	{
	    $classes = array_diff($classes, array('hentry'));
		
	    return $classes;
	}
}
?>