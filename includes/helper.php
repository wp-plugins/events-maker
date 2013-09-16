<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Events_Maker_Helper
{
	public function __construct()
	{
	}


	/**
	 * 
	*/
	public function is_valid_date($post_date)
	{
		$date = explode('-', $post_date);

		if(count($date) === 3)
		{
			if(checkdate($date[1], $date[2], $date[0]) === TRUE)
				return TRUE;
			else
				return 'wrong_date';
		}
		else
			return 'wrong_date_input';
	}


	/**
	 * 
	*/
	public function is_valid_time($post_time)
	{
		$time = explode(':', $post_time);

		if(count($time) === 2)
		{
			$hours = $minutes = array();

			for($i = 0; $i <= 23; $i++)
			{
				$hours[] = (string)(($i < 10 ? '0' : '').$i);
			}

			for($i = 0; $i <= 59; $i++)
			{
				$minutes[] = (string)(($i < 10 ? '0' : '').$i);
			}

			if(in_array($time[0], $hours, TRUE) && in_array($time[1], $minutes, TRUE))
				return TRUE;
			else
				return 'wrong_time';
		}
		else
			return 'wrong_time_input';
	}


	/**
	 * 
	*/
	public function is_valid_datetime($date)
	{
		$datetime = explode(' ', $date);
		$no = count($datetime);

		if($no === 1)
			$format = 'Y-m-d';
		elseif($no === 2)
		{
			$not = count(explode(':', $datetime[1]));

			if($not === 2)
				$format = 'Y-m-d H:i';
			elseif($not === 3)
				$format = 'Y-m-d H:i:s';
			else
				return FALSE;
		}
		else
			return FALSE;

		return (date($format, strtotime($date)) === $date ? $format : FALSE);
	}


	/**
	 * 
	*/
	public function is_after_date($date_before, $date_after)
	{
		return ((strtotime($date_before) > strtotime($date_after)) ? FALSE : TRUE);
	}
}
?>