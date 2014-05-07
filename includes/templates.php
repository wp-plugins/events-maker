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
		add_filter('template_include', array(&$this, 'set_template'));
	}


	/**
	 * Template fallback
	*/
	public function set_template($template)
	{
		if(!$this->options['templates']['default_templates'])
			return $template;

		if(is_post_type_archive('event') && !$this->is_template($template, 'archive'))
			$template = EVENTS_MAKER_PATH.'templates/archive-event.php';

		if(is_tax('event-category') && !$this->is_template($template, 'event-category'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-category.php';

		if($this->options['general']['use_tags'] && is_tax('event-tag') && !$this->is_template($template, 'event-tag'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-tag.php';

		if(is_tax('event-location') && !$this->is_template($template, 'event-location'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-location.php';

		if($this->options['general']['use_organizers'] && is_tax('event-organizer') && !$this->is_template($template, 'event-organizer'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-organizer.php';

		if(is_singular('event') && !$this->is_template($template, 'event'))
			$template = EVENTS_MAKER_PATH.'templates/single-event.php';

		return $template;
	}


	/**
	 * 
	*/
	private function is_template($template_path, $context = '')
	{
		$template = basename($template_path);

		switch($context)
		{
			case 'event';	
				return ($template === 'single-event.php');

			case 'archive':
				return ($template === 'archive-event.php');

			case 'event-category':
				return (preg_match('/^taxonomy-event-category((-(\S*))?).php/', $template) === 1);

			case 'event-tag':
				if($this->options['general']['use_tags'])
					return (preg_match('/^taxonomy-event-tag((-(\S*))?).php/', $template) === 1);
				else
					return false;

			case 'event-location':
				return (preg_match('/^taxonomy-event-location((-(\S*))?).php/', $template) === 1);

			case 'event-organizer':
				if($this->options['general']['use_organizers'])
					return (preg_match('/^taxonomy-event-organizer((-(\S*))?).php/', $template) === 1);
				else
					return false;
		}

		return false;
	}
}
?>