<?php
	class Locations_Plugin_Geocoder
	{
		var $google_geocoder_api_key = '';

		function __construct($google_geocoder_api_key)
		{
			$this->google_geocoder_api_key = $google_geocoder_api_key;
		}		

		function geocode_address($address)
		{
			if ( empty($address) || empty($this->google_geocoder_api_key) ) {
				return false;
			}
			
			$params = array('address' => urlencode($address),
							'key' => $this->google_geocoder_api_key);
			$param_str = build_query($params);
			$api_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $param_str;
			$api_response = wp_remote_post($api_url);//TBD: better processing of API response, such as Query Limits, Referrer Not Allowed, API not Setup Correctly, etc
			
			if (is_wp_error($api_response)) {
				return false; // TBD: do we need to throw an error here?
			}
			
			// geocode worked! pull out the lat and lng values from the response, and return them as an array
			$api_json = json_decode($api_response['body']);
			if ($api_json) {
				$lat = isset($api_json->results[0]->geometry->location->lat) ? $api_json->results[0]->geometry->location->lat : '';
				$lng = isset($api_json->results[0]->geometry->location->lng) ? $api_json->results[0]->geometry->location->lng : '';
				if ($lat == '' || $lng == '') {
					return false;
				} else {			
					return array('lat' => $lat, 'lng' => $lng);
				}
			}
			else {
				// something went wrong! TBD: dig into the specific errors Google can return, and see if any should be bubbled up to the user
				return false;
			}
		}
		
		
		/* Performs a test Geocode with the specified Google API key.
		 *
		 * @param bool Whether to use the cached value, if present. Default: true.
		 *
		 * @returns mixed true if key is working and test succeeds;
		 *				  a string containing the error message on failure.
		 */		
		function test_api_key( $skip_cache = false)
		{
			if ( empty($this->google_geocoder_api_key) ) {
				return 'No API key entered.';
			}

			$transient_val = get_transient('locations_test_google_maps_api_key');
			
			// look for a cached success value (unless caching is being skipped)
			if ( !$skip_cache && !empty( $transient_val ) ) {
				if ( (md5($this->google_geocoder_api_key) == $transient_val) ) {
					// cached value matches this key, so use it
					return true;
				} else {
					// key has changed, so clear the transient
					delete_transient('locations_test_google_maps_api_key');
				}
			}
			
			// no cached value, so test the Geocoder
			$params = array('address' => 'New York, NY',
							'key' => $this->google_geocoder_api_key);
			$param_str = build_query($params);
			$api_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $param_str;
			$api_response = wp_remote_post($api_url);//TBD: better processing of API response, such as Query Limits, Referrer Not Allowed, API not Setup Correctly, etc
			
			if (is_wp_error($api_response)) {
				return 'Error making remote request. Verify that server can make outgoing HTTPS requests.'; // TBD: do we need to bubble up WP error here?
			}
			
			// Got a response from Google (JSON). Decode it, and then see if it has valid lat/lng, or an error.
			$api_json = json_decode($api_response['body']);
			
			// if it looks like a lat/lng pair, than the Geocode worked! Be sure to cache that success, so we stop testing
			if ($api_json) {
				$lat = isset($api_json->results[0]->geometry->location->lat) ? $api_json->results[0]->geometry->location->lat : '';
				$lng = isset($api_json->results[0]->geometry->location->lng) ? $api_json->results[0]->geometry->location->lng : '';
				if ( !empty($lat) && !empty($lng) ) {
					// test geocode worked! store that success in the cache forever! (or until API key changes)
					$transient_val = md5($this->google_geocoder_api_key);
					set_transient('locations_test_google_maps_api_key', $transient_val, 0);
					return true;
				}
			}

			// something went wrong on Google's end. Try to pass their error message back to the user
			if ( !empty($api_json->error_message) ) {
				return $api_json->error_message;
			}
			
			// Geocode failed with no error.
			return 'Unspecified error from Google API.';
		}
	
	}
