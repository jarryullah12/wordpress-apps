<?php

	class Locations_Plugin_DB
	{
		function __construct()
		{
		}
		
		// returns a list of all locations in the database, sorted by the title, ascending
		function get_all_locations($atts = array())
		{
			//these may be passed in via the shortcode or the widget
			//if not passed in, they need some defaults
			$orderby = !empty($atts['orderby']) ? $atts['orderby'] : 'title';
			$order = !empty($atts['order']) ? $atts['order'] : 'ASC';
			
			$conditions = array('post_type' => 'location',
								'post_count' => -1,
								'orderby' => $orderby,
								'order' => $order,
								'nopaging' => true,
						);
				
			//filter by category, if set to
			if(isset($atts['category']) && strlen($atts['category'] > 0)) {
				$conditions['tax_query'] =	array(
												array(
													'taxonomy' => 'location-categories',
													'field' => 'id',
													'terms' => $atts['category']
												)
											);
			}		
			
			$all_locations = get_posts($conditions);
			return $all_locations;
		}
		
		// returns a list of a location in the database, based on the ID passed
		function get_single_location($id = '')
		{
			$conditions = array('p' => $id,								
								'post_type' => 'location',
								'post_count' => -1,
								'orderby' => 'title',
								'order' => 'ASC',
						);
			$location = get_posts($conditions);	
			return $location;
		}
		
		function get_all_countries()
		{
			global $wpdb;
			$query = "
			SELECT m1.meta_value as country
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}postmeta m1
			  ON ( {$wpdb->prefix}posts.ID = m1.post_id )
			WHERE
			{$wpdb->prefix}posts.post_type = 'location'
			AND {$wpdb->prefix}posts.post_status = 'publish'
			AND ( m1.meta_key = '_ikcf_country' )
			GROUP BY m1.meta_value
			ORDER BY m1.meta_value DESC
			LIMIT 100;
			";
			$countries = $wpdb->get_results( $query );
			return $countries;
		}

		function get_all_states($country = '')
		{
			global $wpdb;
			$maybe_restrict_country = !empty($country)
									  ? sprintf( "AND ( country_meta.meta_key = '_ikcf_country' AND country_meta.meta_value = '%s' )",
												 sanitize_text_field($country) )
									  : "";			
			$query = "
			SELECT state_meta.meta_value as state
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}postmeta state_meta
			  ON ( {$wpdb->prefix}posts.ID = state_meta.post_id )
			INNER JOIN {$wpdb->prefix}postmeta country_meta
			  ON ( {$wpdb->prefix}posts.ID = country_meta.post_id )
			WHERE
			{$wpdb->prefix}posts.post_type = 'location'
			AND {$wpdb->prefix}posts.post_status = 'publish'
			AND ( state_meta.meta_key = '_ikcf_state' AND state_meta.meta_value != '' )
			{$maybe_restrict_country}
			GROUP BY state_meta.meta_value
			ORDER BY state_meta.meta_value DESC
			LIMIT 100;
			";
			$states = $wpdb->get_results( $query );
			return $states;
		}

		function get_all_cities($state = '')
		{
			$state_meta_value = !empty($state)
								? sprintf( "= '%s'", sanitize_text_field($state) )
								: "!= ''";
			
			global $wpdb;
			$query = "
			SELECT city_meta.meta_value as city, state_meta.meta_value as state
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}postmeta city_meta
			  ON ( {$wpdb->prefix}posts.ID = city_meta.post_id )
			INNER JOIN {$wpdb->prefix}postmeta state_meta
			  ON ( {$wpdb->prefix}posts.ID = state_meta.post_id )
			WHERE
			{$wpdb->prefix}posts.post_type = 'location'
			AND {$wpdb->prefix}posts.post_status = 'publish'
			AND ( city_meta.meta_key = '_ikcf_city' )
			AND ( state_meta.meta_key = '_ikcf_state' AND state_meta.meta_value {$state_meta_value} )
			GROUP BY city_meta.meta_value
			ORDER BY city_meta.meta_value DESC,state_meta.meta_value DESC
			LIMIT 100;
			";
			$cities = $wpdb->get_results( $query );
			return $cities;
		}	
	}
	
	