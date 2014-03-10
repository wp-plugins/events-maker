<?php
if(!defined('ABSPATH')) exit; //exit if accessed directly

new Events_Maker_Templates();

class Events_Maker_Templates
{
	private $options = array();


	public function __construct()
	{
		//settings
		$this->options = array_merge(
			array('general' => get_option('events_maker_general')),
			array('templates' => get_option('events_maker_templates'))
		);

		//filters
		add_filter('template_include', array(&$this, 'set_template'));
	}


	/**
	 * Template fallback
	*/
	public function set_template($template)
	{
		if($this->options['templates']['default_templates'] === FALSE)
			return $template;

		if(is_post_type_archive('event') && !$this->is_template($template, 'archive'))
			$template = EVENTS_MAKER_PATH.'templates/archive-event.php';

		if(is_tax('event-category')  && !$this->is_template($template, 'event-category'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-category.php';

		if($this->options['general']['use_tags'] === TRUE)
		{
			if(is_tax('event-tag') && !$this->is_template($template, 'event-tag'))
				$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-tag.php';
		}

		if(is_tax('event-location') && !$this->is_template($template, 'event-location'))
			$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-location.php';

		if($this->options['general']['use_organizers'] === TRUE)
		{
			if(is_tax('event-organizer') && !$this->is_template($template, 'event-organizer'))
				$template = EVENTS_MAKER_PATH.'templates/taxonomy-event-organizer.php';
		}

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
				return (1 === preg_match('/^taxonomy-event-category((-(\S*))?).php/', $template));

			case 'event-tag':
				if($this->options['general']['use_tags'] === TRUE)
					return (1 === preg_match('/^taxonomy-event-tag((-(\S*))?).php/', $template));
				else
					return FALSE;

			case 'event-location':
				return (1 === preg_match('/^taxonomy-event-location((-(\S*))?).php/', $template));

			case 'event-organizer':
				if($this->options['general']['use_organizers'] === TRUE)
					return (1 === preg_match('/^taxonomy-event-organizer((-(\S*))?).php/', $template));
				else
					return FALSE;
		}

		return FALSE;
	}
}
?>