<?php
	class Locations_Plugin_Utils
	{
		function __construct()
		{
		}	

		/* Source: http://stackoverflow.com/a/13646848 */
		function get_real_user_ip()
		{
			if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
					$addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
					return trim($addr[0]);
				} else {
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
			}
			else {
				if(!$_SERVER['REMOTE_ADDR']) {
					return $_SERVER['LOCAL_ADDR'];
				}
				return $_SERVER['REMOTE_ADDR'];
			}
		}
		
		function normalize_truthy_value($input)
		{
			$input = strtolower($input);
			$truthy_values = array('yes', 'y', '1', 1, 'true', true);
			return in_array($input, $truthy_values);
		}
	}
