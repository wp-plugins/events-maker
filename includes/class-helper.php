<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Events_Maker_Helper Class.
 */
class Events_Maker_Helper {

	public function __construct() {
		
	}

	/**
	 * 
	 */
	public function is_valid_date( $post_date ) {
		$date = explode( '-', $post_date );

		if ( count( $date ) === 3 ) {
			if ( checkdate( $date[1], $date[2], $date[0] ) === true )
				return true;
			else
				return false;
		} else
			return false;
	}

	/**
	 * 
	 */
	public function is_valid_time( $post_time ) {
		$time = explode( ':', $post_time );

		if ( count( $time ) === 2 ) {
			$hours = $minutes = array();

			for ( $i = 0; $i <= 23; $i ++  ) {
				$hours[] = (string) (($i < 10 ? '0' : '') . $i);
			}

			for ( $i = 0; $i <= 59; $i ++  ) {
				$minutes[] = (string) (($i < 10 ? '0' : '') . $i);
			}

			if ( in_array( $time[0], $hours, true ) && in_array( $time[1], $minutes, true ) )
				return true;
			else
				+ false;
		} else
			return false;
	}

	/**
	 * 
	 */
	public function is_valid_datetime( $date ) {
		// is that a date
		$parsed_date = date_parse( (string) $date );

		if ( ! $parsed_date )
			return false;

		// if this is a date in an expected format
		if ( empty( $parsed_date['errors'] ) ) {
			
			$datetime = explode( ' ', (string) $date );
			$no = count( $datetime );

			if ( $no === 1 ) {
				$format = 'Y-m-d';
			} elseif ( $no === 2 ) {
				$not = count( explode( ':', $datetime[1] ) );

				if ( $not === 2 ) {
					$format = 'Y-m-d H:i';
				} elseif ( $not === 3 ) {
					$format = 'Y-m-d H:i:s';
				} else {
					return false;
				}
			} else {
				return false;
			}
			
		} else {
			
			// if this is a date but in a different format
			$format = 'Y-m-d H:i:s';
			
		}

		return $format;
	}

	/**
	 * 
	 */
	public function is_after_date( $date_before, $date_after, $strict = true ) {
		if ( $strict )
			return ( ( strtotime( $date_before ) > strtotime( $date_after ) ) ? true : false );
		else
			return ( ( strtotime( $date_before ) >= strtotime( $date_after ) ) ? true : false );
	}

}