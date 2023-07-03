<?php
	class Locations_Plugin_Store_Locator
	{
		var $DB;
		
		function __construct( $root, $DB, $Geocoder, $Utils )
		{
			$this->root = $root;
			$this->DB = $DB;
			$this->Geocoder = $Geocoder;
			$this->Utils = $Utils;
			$this->add_hooks();
		}		

		function add_hooks()
		{
			//add extra clause to queries that handle lat/lng
			add_filter('get_meta_sql',array($this,'cast_decimal_precision'));

			add_shortcode('store_locator', array($this, 'store_locator_shortcode'));
		}		

		/* output a store locator search box, and/or a list of results */
		function store_locator_shortcode($atts, $content = '')
		{		
			// merge any settings specified by the shortcode with our defaults
			$defaults = array(	'caption' => '',
								'style' =>	'small',
								'show_photos' => 'true',
								'id' => 'locations_pro_search_form',
								'class' => 'store_locator',
								'show_all_locations' => false,
								'show_all_nearby_locations' => true,
								'show_category_select' => false,
								'show_search_radius' => false,
								'show_search_results' => true,
								'link_search_results' => false,
								'map_width' => '100%',
								'map_height' => '500px',
								'map_class' => '',
								'caption_class' => 'store_locator_caption',
								'search_button_class' => 'btn btn-search',
								'search_button_label' => 'Search',
								'input_wrapper_class' => 'input_wrapper',
								'search_input_label' => 'Your Location:',
								'search_input_id' => 'your_location',
								'search_input_class' => '',
								'search_input_placeholder' => 'Your Location',
								'search_again_label' => 'Try Your Search Again:',
								'search_again_class' => 'search_again',
								'category_select_id' => 'location_category',
								'category_select_label' => 'Category:',
								'category_select_description' => 'Leave empty to show All Locations.',
								'allow_multiple_categories' => true,
								'radius_select_label' => 'Search Within:',
								'radius_select_id' => 'search_radius',
								'search_box_location' => 'below',
								'default_latitude' => get_option('loc_p_default_latitude', '39.8282'), // defaults to USA
								'default_longitude' => get_option('loc_p_default_longitude', '-98.5795'), // defaults to USA
								'search_results_style' => 'list',
								'compact_view' => false,
								'store_locator_theme' => 'light_theme'
							);
			$atts = shortcode_atts($defaults, $atts);	
			
			if ( $atts['search_results_style'] == 'tile' ) {
				$atts['class'] .= ' locations';
			}		
			
			if ( !empty($atts['compact_view']) ) {
				$atts['class'] .= ' compact '. $atts['store_locator_theme'];
				$atts['search_box_location'] = 'above';
			}

			$this->shortcode_atts = $atts;
			
			// start the HTML output with a wrapper div
			$id_str = sprintf(' id="%s"', $atts['id']);
			$class_str = sprintf(' class="%s"', $atts['class']);
			$style_str = !empty( $atts['map_width'] )
						 ? sprintf( 'style="width:%s;"', $atts['map_width'] )
						 : '';
			$html = sprintf('<div %s %s %s>', $id_str, $class_str, $style_str);

			// add the caption, if one was specified
			if (strlen($atts['caption']) > 1) {
				$html .= sprintf( '<h2 class="%s">%s</h2>', $atts['caption_class'], htmlentities($atts['caption']) );
			}
				
			// add the search form
			if ( in_array($atts['search_box_location'], array('above', 'top', 'both')) ) {
				
				//add the search again label above the results and form, if the search box is also displayed above the results and a search has been performed
				if (isset($_REQUEST['search_locations']) && isset($_REQUEST['your_location']) && strlen(trim($_REQUEST['your_location'])) > 0) {
					$html .= sprintf('<h3 class="%s">%s</h3>', $atts['search_again_class'], $atts['search_again_label']);
				}
				
				$current_search = isset( $_REQUEST['your_location'] ) 
								  ? sanitize_text_field($_REQUEST['your_location'])
								  : '';
				$html .= $this->store_locator_search_form_html($current_search, $atts['show_category_select'], $atts['show_search_radius']);
			}

			// if a search was requested, perform it now and show the results
			if (isset($_REQUEST['search_locations']) && isset($_REQUEST['your_location']) && strlen(trim($_REQUEST['your_location'])) > 0)
			{
				//attempt to load radius from search form
				$the_radius = false;
				if (isset($_REQUEST['search_radius'])) {
					$the_radius = intval($_REQUEST['search_radius']);
				} else {
					$the_radius = intval( get_option('loc_p_search_radius', 25) );
				}

				if ($the_radius == 0) {
					$the_radius = 25;
				}

				//attempt to load category from search form
				$the_category = ! empty($_REQUEST['location_category'])
								? sanitize_text_field($_REQUEST['location_category'])
								: '';
				
				// perform the search
				$your_location = ! empty($_REQUEST['your_location'])
								? sanitize_text_field($_REQUEST['your_location'])
								: '';
				$radius_miles = $this->get_search_radius_in_miles($the_radius);
				$radius_pretty = get_option('loc_p_search_radius');
				$nearest_locations = $this->find_nearest_locations($your_location, $radius_miles, $origin, $the_category); // second param is radius
				
				// generate the SERP (or the message saying "no results found")
				$html .= $this->store_locator_results_html( $your_location, $the_radius, $nearest_locations, $origin, $atts['show_search_results'], $atts['search_results_style'], $atts['compact_view'] );
			}
			else 
			{
				// a search hasn't yet been performed, display optional map with all locations
				$locations_to_plot = array();
				$origin = false;

				// look for an origin passed in via query string
				if (!empty($_REQUEST['sl_origin'])) {
					$starting_address = sanitize_text_field($_REQUEST['sl_origin']);
					$origin = $this->Geocoder->geocode_address($starting_address); // $origin will be false if geocode fails
					$your_location = $origin ? $starting_address : ''; // show provided value in the search input, but only if its a valid address
				}

				// no origin specified; try to geolocate them by IP address
				if (!$origin) {
					$start_array = $this->get_starting_lat_lng($atts['default_latitude'], $atts['default_longitude']);
					$origin = array('lat' => $start_array['latitude'],
									'lng' => $start_array['longitude']);
					$starting_address = !empty($start_array['city']) && !empty($start_array['state'])
										? esc_html($start_array['city']) . ", " . esc_html($start_array['state'])
										: '';
					$your_location = $starting_address; // show the geolocated address in the input, by storing it in $your_location
				}
				
				// load the locations to be shown (all or nearby)
				if($atts['show_all_locations']) {
					foreach ( $this->DB->get_all_locations() as $location ) {
						// convert to array, add lat + lng
						$locations_to_plot[] = $this->root->get_location_metadata($location->ID);
					}			
				}
				else if($atts['show_all_nearby_locations']) {
					$radius_miles = $this->get_search_radius_in_miles();
					$locations_to_plot = $this->find_nearest_locations($origin, $radius_miles);
				}
				
				$map_html = $this->build_map_html_for_nearby_locations($locations_to_plot, $origin);
				$style_str = !empty($this->shortcode_atts) && !empty( $this->shortcode_atts['map_height'] )
							 ? sprintf('style="height: %s;"', rtrim($this->shortcode_atts['map_height'], ';') )
							 : '';
				
				if ( !empty($atts['compact_view']) ) {
					$tmpl = '<div class="store_locator_wrapper" %s><div class="left_col">%s</div><div class="right_col">%s</div></div>';
					$html .= sprintf( $tmpl, $style_str, '', $map_html);
				} else {
					$tmpl = '<div class="store_locator_wrapper">%s</div>';
					$html .= sprintf( $tmpl, $map_html);
				}
			}

			// add the search form
			if ( in_array($atts['search_box_location'], array('below', 'bottom', 'both')) ) {
				//add the search again label below the results, if the search box is also displayed below the results and a search has been performed
				if (isset($_REQUEST['search_locations']) && isset($_REQUEST['your_location']) && strlen(trim($_REQUEST['your_location'])) > 0) {
					$html .= sprintf('<h3 class="%s">%s</h3>', $atts['search_again_class'], $atts['search_again_label']);
				}
				$current_search = isset($your_location) ? htmlentities($your_location) : '';
				$html .= $this->store_locator_search_form_html($current_search, $atts['show_category_select'], $atts['show_search_radius']);
			}
			
			// close the store_locator div and return the finished HTML
			$html .= '</div>'; // <!--.store_locator-->
			return $html;		
		}
		
	/*
	 * Outputs a the Store Locator search form. If a search has just been run, 
	 * it will output a heading (i.e., "Try Your Search Again:") before 
	 * the search form.
	 *
	 * @param array $atts The attributes array for the shortcode calling this 
	 *					  function. Must include keys: search_again_class, 
	 *					  search_again_label, show_category_select, 
	 *					  show_search_radius.
	 */
	function store_locator_search_form($atts)
	{			
		$html = '';
		$query = $this->get_search_query();
		
		// add the search again label if a search has been performed
		$heading = !empty($query)
				   ? sprintf('<h3 class="%s">%s</h3>', $atts['search_again_class'], $atts['search_again_label'])
				   : '';		
		$heading = apply_filters('locations_search_form_heading', $heading, $query);
		
		$form = $this->store_locator_search_form_html( htmlentities($query), $atts['show_category_select'], $atts['show_search_radius'] );		
		return $heading . $form;
	}		
		
		function get_search_query()
		{
			$query = '';
			if ( !empty($_REQUEST['search_locations']) && !empty($_REQUEST['your_location']) ) {
				$query = sanitize_text_field($_REQUEST['your_location']);
			}
			return apply_filters('locations_get_search_query', $query);
		}
		
		/*
		* Increases the decimal precision on WP queries to 6. This is required 
		* to receive meaningful results for the store locator..
		*
		* @param array $arr_query Array representing the current meta query 
		*						  (from WP's get_meta_sql hook)
		*
		* @return array The meta query array, with decimal precision increased.
		*/
		function cast_decimal_precision( $arr_query )
		{
			$search_pos = strpos($arr_query['where'], 'DECIMAL');
			$inserted_pos = strpos($arr_query['where'], 'DECIMAL(10,6)');
			
			// Be careful not to add this clause twice!
			if( ($search_pos !== false) && ($inserted_pos === false) ) {
				$arr_query['where'] = str_replace('DECIMAL','DECIMAL(10,6)', $arr_query['where']);
			}

			return $arr_query;
		}
		
		/*
		 * Receives AJAX searches from the front-end and returns formatted lists
		 * of search results. Expects POST values for: 'query', 'radius', 'category', 
		 * and 'page'.
		 */
		function handle_ajax_searches()
		{
			// collect search params
			$query = filter_input( INPUT_POST, 'query', FILTER_SANITIZE_STRING );
			$radius = filter_input( INPUT_POST, 'radius', FILTER_SANITIZE_NUMBER_INT );
			$category = filter_input( INPUT_POST, 'category', FILTER_SANITIZE_STRING );
			$page = filter_input( INPUT_POST, 'paged', FILTER_SANITIZE_STRING );
			
			// locations_pro_ajax_search
			$nearest_locations = $this->get_search_results($query, $radius, $category, $origin, $page);
			
			$list_html = $this->build_search_results_html( $nearest_locations );
			$nearby_count = count($nearest_locations);
			$results_message = $nearby_count > 0 
							   ? sprintf( '<span class="results_count">%d</span> location%s found near <span class="query">%s</span>', $nearby_count, ($nearby_count > 1 ? 's' : ''), htmlentities($query) )
							   : sprintf( 'No locations found near %s.', htmlentities($query) );
			$results_message = apply_filters( 'locations_store_locator_results_found_text', $results_message, $nearby_count, $nearest_locations, $query );
			$message_html = sprintf('<p class="results_found_message"><strong>%s</strong></p>', $results_message);

			$markers = array_map( array($this, 'build_marker_data'), $nearest_locations );

			$response = array(
				'message_html' => $message_html,
				'list_html' => $list_html,
				'result_count' => count($nearest_locations),
				'origin' => $origin,
				'markers' => $markers,
			);
			
			wp_die( json_encode($response) );
		}
		
		function get_search_results($query, $radius = '', $category = '', &$origin = '', $page = 1)
		{
			// if radius was not specified, fall back to the plugin's default
			if ( empty($radius) ) {
				$radius = intval( get_option('loc_p_search_radius', 25) );
			}

			// perform the search
			return $this->find_nearest_locations(
				$query,
				$this->get_search_radius_in_miles($radius), // maybe convert from km
				$origin, // passed by ref, so must be a variable
				$category
			);		
		}		

		function build_map_html_for_nearby_locations($locations, $origin)
		{
			$html = $this->get_map_canvas_html();			
			$markers = array_map( array($this, 'build_marker_data'), $locations );
			
			// add JS variables with the marker data, so we can render it on the map
			$html .= $this->location_data_js($markers, $origin);
			
			return $html;
		}
		
		function get_map_canvas_html()
		{
			$atts = !empty($this->shortcode_atts) ? $this->shortcode_atts : array();
			$style_str = '';
			$style_str .= (!empty($atts['map_width'])) ? ' width: ' . rtrim($atts['map_width'], ';') . ';' : '';
			$style_str .= (!empty($atts['map_height'])) ? ' height: ' . rtrim($atts['map_height'], ';') . ';' : '';
			$style_str = ' style="' . trim($style_str) . '; border: 1px solid #ccc;"';
			
			$class_str = (!empty($atts['map_class'])) ? ' class="' . $atts['map_class'] . ' map-canvas"' : 'class="map-canvas"';		
			$map_id = 'map-canvas' .  rand(1,10000);
			$template =  '<div id="%s" %s %s></div>';
			return sprintf($template, $map_id, $class_str, $style_str);
		}

		function build_search_results_html( $nearest_locations, $search_results_style = '' )
		{
			$search_results_class = 'locations_search_results'; 
			$search_results_class .= ( $search_results_style == 'tile' )
									 ? ' tile'
									 : '';
			$search_results_class = apply_filters('locations_search_results_class', $search_results_class, $search_results_style, $nearest_locations);		
			$html = sprintf('<ol class="%s">', $search_results_class);
			foreach ( $nearest_locations as $loc )
			{			
				switch( $search_results_style ) {
					case 'tile':
						$loc_obj = (object) $loc;
						$loc_obj->post_title = $loc_obj->title;
						$atts = array(
							'map_width' => '100%',
						);
						$location_html = $this->root->build_location_html($loc_obj, $atts);
						$item_html = sprintf('<li>%s</li>', $location_html);
						break;
					default:
						$item_html = $this->store_locator_item_html( $loc, $this->shortcode_atts['link_search_results'] );
						break;
				}			
				$html .= apply_filters('locations_search_results_item_html', $item_html, $loc);
			}
			$html .= '</ol>';		
			return $html;
		}
		
		// add the search form
		function store_locator_search_form_html($current_search = '', $show_category_select = false, $show_search_radius = false)
		{		
			$miles_or_km = get_option('loc_p_miles_or_km', 'miles');
			$location_categories = get_terms( 'location-categories', 'orderby=title&hide_empty=0' );	
			
			// begin the form
			$html = '';
			$extra_params = array(
				'search_locations' => '1',
				'nocache' => substr(md5(rand()), 0, 10),
			);
			$search_url = add_query_arg( $extra_params ); // built in WP function, adds our arguments to the current URL (IMPORTANT: URL MUST STILL BE ESCAPED!!!)
			$search_url .= '#' . $this->shortcode_atts['id']; // add ID fragment to URL so that we jump down to the form upon searching
			$extra_classes = implode($this->get_search_form_classes(), ' ');
			$html .= sprintf('<div class="store_locator_search_form_wrapper %s">', $extra_classes);
				$html .= sprintf('<form method="POST" action="%s">', esc_url($search_url));
					// add search input
					$html .= sprintf('<div class="store_locator_query %s">', $this->shortcode_atts['input_wrapper_class']);
						$html .= sprintf('<label for="%s">%s</label>', $this->shortcode_atts['search_input_id'], $this->shortcode_atts['search_input_label']);
						$html .= sprintf('<input name="your_location" id="%s" class="%s" placeholder="%s" type="text" value="%s" />', $this->shortcode_atts['search_input_id'], $this->shortcode_atts['search_input_class'], $this->shortcode_atts['search_input_placeholder'], htmlentities($current_search));
					$html .= '</div>';
					
					// add search radius dropdown
					if($show_search_radius) {
						$html .= $this->get_search_radius_select($miles_or_km);
					}
					
					// add category select dropdown
					if($show_category_select) {					
						$html .= $this->get_search_category_select($location_categories);
					}

					// add the submit button
					$search_button_class_str = sprintf(' class="%s"', $this->shortcode_atts['search_button_class']);			
					$html .= sprintf('<div class="%s submit_wrapper">', $this->shortcode_atts['input_wrapper_class']);
						$html .= sprintf('<button type="submit" %s>%s</button>', $search_button_class_str, $this->shortcode_atts['search_button_label']);
					$html .= '</div>';
				$html .= '</form>';
			$html .= '</div>';
			return $html;
		}
		
		/*
		 * Returns an array of classes to add to the store locators search form
		 * wrapper representing the selected attributes.
		 */
		function get_search_form_classes()
		{
			$classes = array();
			$classes[] = !empty($this->shortcode_atts['show_category_select'])
						 ? 'show_category_select'
						 : '';

			$classes[] = !empty($this->shortcode_atts['show_search_radius'])
						 ? 'show_search_radius'
						 : '';								 
			return $classes;
		}
		
		function get_starting_lat_lng($default_latitude, $default_longitude)
		{		
			$geo = $this->geolocate_current_visitor();
			// if geocoding fails, fall back to default
			if ( empty($geo) || empty($geo['latitude']) || empty($geo['longitude']) ) {
				//cast geo to an array in case the geolocator returned false, to prevent warning
				$geo = array();
				
				$geo['latitude'] = $default_latitude;
				$geo['longitude'] = $default_longitude;
			}
			return $geo;		
		}
	
		function store_locator_item_html($loc, $link_title = false)
		{
			$html = '';
			$addr = htmlentities($loc['street_address']);
			$miles_or_km = (get_option('loc_p_miles_or_km', 'miles') == 'miles') ? 'miles' : 'kilometers';
			if (isset($loc['street_address_line_2']) && strlen($loc['street_address_line_2']) > 0) {
				$addr .= '<br />' . htmlentities($loc['street_address_line_2']);
			}
			
			$location_id = 'location_item_' . $loc['ID'];
			$html .= sprintf('<li id="%s" class="location noPhoto">', $location_id);
				if ($link_title) {
					// link the title (specified in the shortcode)
					$html .= sprintf('<h3><a href="%s">%s</a></h3>', get_permalink($loc['ID']), $loc['title']);
				} else {
					// don't link the title (default)
					$html .= sprintf('<h3>%s</h3>', $loc['title']);
				}
							
				$html .= '<div class="address"><div class="addr">' . $addr . '</div><div class="city_state_zip"><span class="city">' . htmlentities($loc['city']) . ', <span class="state">' . htmlentities($loc['state']) . ' <span class="zipcode">' . htmlentities($loc['zipcode']) . '</span></span></span></div></div>';
				$html .= '<div class="phone-wrapper"><strong>Phone:</strong> <span class="num phone">' . htmlentities($loc['phone']) . '</span></div>';
				$html .= '<div class="distance-wrapper"><em>' 
							. htmlentities($loc['distance']) . ' ' . $miles_or_km . ' away'
						 . '</em></div>';

				$directions_link = get_option('loc_p_show_directions_links', true)
								   ? $this->build_directions_link($loc)
								   : '';								

				if ( !empty($directions_link) ) {
					$html .= sprintf('<div class="directions-wrapper">%s</div>', wp_kses( $directions_link, 'post' ));
				}

			$html .= '</li>';	
			
			return apply_filters('store_locator_item_html', $html, $loc);
		}
		

		/*
		 * Returns the HTML <select> box containing the search radius options.
		 * Note: The default_radius option will be selected, 
		 * 		 unless specified in the query string (i.e., after a search)
		 */
		function get_search_radius_select($miles_or_km)
		{
			$html = sprintf('<div class="store_locator_radius %s">', $this->shortcode_atts['input_wrapper_class']);
				$html .= sprintf('<label for="%s">%s</label>', $this->shortcode_atts['radius_select_id'], $this->shortcode_atts['radius_select_label']);
				$html .= sprintf('<select name="search_radius" id="%s">', $this->shortcode_atts['radius_select_id']);
					$default_radius = intval( get_option('loc_p_search_radius', 0) );
					$options = $this->get_search_radius_options($default_radius);
					
					if( isset($_REQUEST['search_radius']) && intval($_REQUEST['search_radius']) > 0 ) {
						$current_option = intval($_REQUEST['search_radius']);
					} else {
						$current_option = $default_radius;
					}
					
					$template = '<option value="%s" %s>%s</option>';
					foreach($options as $index => $distance) {
						$selected = ($distance == $current_option) ? 'selected="selected"' : '';
						$label = $distance . ' ' . $miles_or_km;
						$html .= sprintf($template, $distance, $selected, $label);
					}
					
				$html .= '</select>';
			$html .= '</div>';		
			return $html;
		}
		
		/*
		 * Returns an array of the possible options for the search radius drop down
		 * Keys represent the numeric value of the options, while the value is left blank
		 */
		function get_search_radius_options($default_radius)
		{
			$options = array(
				'5',
				'10',
				'25',
				'50',
				'100',
				'500',
			);
					
			// make sure the default radius is included	
			// (and it must be between 0 and 1000 to be included)
			if ($default_radius == 0) {
				$default_radius = 10;
			}

			if ($default_radius > 1000) {
				$default_radius = 1000;
			}		

			// if default_radius is not in options, add it now
			if ( !in_array($default_radius, $options) ) {
				$options[] = $default_radius;			
				sort($options, SORT_NUMERIC); // need to resort the array now 
			}
					
			// provide an opportunity for the user to override the options, then return them
			return apply_filters('locations_search_radius_options', $options);
		}
		
		function get_search_category_select($location_categories) //, $select_label = '', $description = '', $allow_multi = true, $input_wrapper_class = '')
		{
			$select_label = $this->shortcode_atts['category_select_label'];
			$select_description = $this->shortcode_atts['category_select_description'];
			$allow_multi = $this->shortcode_atts['allow_multiple_categories'];
			$input_wrapper_class = $this->shortcode_atts['input_wrapper_class'];
			
			// TODO: wire up selected so it respects the current category
			// TODO: replace mutiselect with checkboxes
			$selected = '';
			$multi_str = ($allow_multi ? ' multiple="multiple"': '');		

			$html = sprintf('<div class="store_locator_category %s">', $this->shortcode_atts['input_wrapper_class']);
				$html .= sprintf('<label for="%s">%s</label>', $this->shortcode_atts['category_select_id'], $select_label);	
				$html .= sprintf('<select name="location_category[]" id="%s" %s>', $this->shortcode_atts['category_select_id'], $multi_str);
				$html .= '<option value="">All Categories</option>';
				foreach($location_categories as $cat) {
					$html .= '<option value="' . $cat->slug . ' " ' . $selected . '>' . $cat->name . '</option>';
				}
				$html .= '</select>';
				if (!empty($select_description)) {
					$html .= sprintf('<p class="description">%s</p>', $select_description);
				}
			$html .= '</div>';
			
			return $html;
		}
		
		/* Given a starting address, returns all locations within the specified radius, sorted by distance from the starting address (closest location first)
		 * Note: this function assumes that the locations have already been geocoded
		*/
		function find_nearest_locations($starting_address, $radius_in_miles, &$origin = false, $category = '')
		{
			global $wpdb;
		
			// get starting coordinates based on the starting address
			// note: if $origin is a string, we assume its an address we need to geocode
			// 		 if $origin is an array, we assume it is lat and lng (already geocoded)
			if (!is_array($starting_address)) {
				$origin = $this->Geocoder->geocode_address($starting_address);
			} else {
				$origin = $starting_address;
			}
			
			if ($origin === FALSE) {
				return false; // invalid address! should this raise an error? (TBD)
			}
			
			// calculate the acceptable ranges for latitude/longitude
			$lat_range = $radius_in_miles/69.172;
			$lon_range = $radius_in_miles/(cos(deg2rad($origin['lat'])) * 69.172);
			$min_lat = number_format($origin['lat'] - $lat_range, "6", ".", "");
			$max_lat = number_format($origin['lat'] + $lat_range, "6", ".", "");
			$min_lng = number_format($origin['lng'] - $lon_range, "6", ".", "");
			$max_lng = number_format($origin['lng'] + $lon_range, "6", ".", "");
			
			return $this->find_locations_within_bounds($min_lat, $max_lat, $min_lng, $max_lng, $origin, $category);
			
		}
		
		function find_locations_within_bounds($min_lat, $max_lat, $min_lng, $max_lng, $origin, $category = '')
		{
			//TBD: support paginating search results		
			
			$args = array(
				'post_type' => 'location',
				'meta_query' => array(
					array(
						'key' => '_ikcf_latitude',
						'value' => array ($min_lat, $max_lat) ,
						'type' => 'DECIMAL',
						'compare' => 'BETWEEN'
					),
					array(
						'key' => '_ikcf_longitude',
						'value' => array ($min_lng, $max_lng) ,
						'type' => 'DECIMAL',
						'compare' => 'BETWEEN'
					)
				),
				'posts_per_page' => -1,
				'nopaging' => true,
				'suppress_filters' => false
			);
			
			// add category parameter to query if needed
			if( !empty($category) ) {
				// Important: tax_query has to be an *array inside an array*			
				// Without the double-array it will be ignored
				$args['tax_query'] = 
					array(
						array(
							'taxonomy' => 'location-categories',
							'field'    => 'slug',
							'terms'    => trim($category)
						)
					);
			}
			
			// see if any locations match. if so, return the results. if not, return an empty array
			$query = new WP_Query( $args );		
			if ( $query->have_posts() ) {
				// We found some locations! 
				// now, lets pull them out of the WP_Query object and into an array
				// then we'll also sort them by distance from the origin, and add that distance to the array so it can be shown in the SERP
				$all_locations = array();
				while ( $query->have_posts() )
				{
					$query->next_post();
					$myId = $query->post->ID;
					$loc = $this->root->get_location_metadata($myId);
								
					// calculate the distance, and add it as a key
					$miles_or_km = get_option('loc_p_miles_or_km', 'miles');
					$loc['distance'] = $this->distance_between_coords($loc['lat'], $loc['lng'], $origin['lat'], $origin['lng'], $miles_or_km);

					// add this location to the unsorted list
					$all_locations[] = $loc;				
				}			
				
				// Restore original Post Data, so as not to mess up any other Loops
				wp_reset_postdata();			
				
				// sort the list of locations by their distance keys, and then return the sorted list
				usort($all_locations, array($this, 'sort_by_distance'));
				return $all_locations;
			} else {
				return array();
			}
		}
		
		/* Sorts an array by its 'distance' key.
		 * Used for sorting the store locator's search results by their distance from the origin 
		 */
		function sort_by_distance($a, $b)
		{
			if ($a['distance'] == $b['distance']) {
				return 0;
			}
			return ($a['distance'] < $b['distance']) ? -1 : 1;
		}
		
		// calculates approximate distance between 2 lat/lng pairs, using the haversine formula
		function distance_between_coords( $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $milesOrKm = 'miles')
		{
			// constants
			$earthRadius_meters = 6371000;
			$feetPerMeter = 3.2808399;
			$feetPerMile = 5280;
			
			// convert from degrees to radians
			$latFrom = deg2rad($latitudeFrom);
			$lonFrom = deg2rad($longitudeFrom);
			$latTo = deg2rad($latitudeTo);
			$lonTo = deg2rad($longitudeTo);

			// calculate the distance between the points in radians
			$latDelta = $latTo - $latFrom;
			$lonDelta = $lonTo - $lonFrom;

			// using the haversine formula, calculate the angular distance travelled and then convert it into meters
			$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +	cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
			$distance_meters = $angle * $earthRadius_meters;
			
			// we have distance in meters, but we need it in miles or kilometers), so we convert it now
			if ($milesOrKm == 'miles')
			{
				$distance_feet = floatval($distance_meters) * $feetPerMeter;
				$distance_miles = ($distance_feet / $feetPerMile);
				
				// round the result (in miles) to 2 decimal places, and return it
				$pretty_miles = number_format($distance_miles, "2", ".", "");
				return $pretty_miles;
			}
			else // km
			{
				$distance_kilometers = $distance_meters / 1000;
				$pretty_distance = number_format($distance_kilometers, "2", ".", "");
				return $pretty_distance;
			}		
		}
		
		function store_locator_results_html($your_location, $radius, $nearest_locations, $origin = false, $show_search_results = true, $search_results_style = '', $compact_view = false)
		{
			$markers = array();
			$html = '';

			// if results were found, output the serp
			if ( $nearest_locations  !== FALSE && count($nearest_locations) > 0 )
			{
				if ($show_search_results) {
					// include labels, list, etc - the whole shebang
					$message_html = '<p class="results_found_message"><strong>Locations nearest to ' . htmlentities($your_location) . '</strong></p>';
					$map_html = $this->get_map_canvas_html();
					$list_html = $this->build_search_results_html( $nearest_locations, $search_results_style );
					if ( $compact_view ) {
						$tmpl = '<div class="store_locator_wrapper"><div class="left_col">%s %s</div><div class="right_col">%s</div></div>';
						$html .= sprintf($tmpl, $message_html, $list_html, $map_html);				
					} else {
						$tmpl = '<div class="store_locator_wrapper">%s %s %s</div>';
						$html .= sprintf($tmpl, $message_html, $map_html, $list_html);
					}
				}
				else {
					// map only, no caption or list. does include search box, of course. (its added later by another function)
					$html .= $this->get_map_canvas_html();
				}

				$markers = array_map( array($this, 'build_marker_data'), $nearest_locations );
				
				// pass the origin and markers data to the page, so that it can be rendered on the map
				$html .= $this->location_data_js($markers, $origin);
			}
			else
			{
				$miles_or_km = (get_option('loc_p_miles_or_km', 'miles') == 'miles') ? 'miles' : 'kilometers';
				$html .= '<p class="no_locations">No locations found within ' . htmlentities($radius) . ' ' . $miles_or_km . ' of ' . htmlentities($your_location) .'.</p>';
			}	
			return apply_filters('locations_search_results_html', $html);
		}
		
		function location_data_js($markers, $origin)
		{
			$html = '';
			$html .= '<script type="text/javascript">';
			$html .= 'var $_gp_map_locations = ' . json_encode($markers) . ';';
			$html .= 'var $_gp_map_center = ' . json_encode($origin) . ';';
			// output the maps template as well
			$html .= 'var $_gp_map_info_window_template = ' . json_encode($this->get_google_maps_info_window_template()) . ';';
			$html .= '</script>';	
			return $html;
		}
		
		function build_marker_data($loc)
		{
			$js_address = $loc['street_address'];
			if (isset($loc['street_address_line_2']) && strlen($loc['street_address_line_2']) > 0) {
				$js_address .= "\n" . $loc['street_address_line_2'];
			}
			$js_address .= "<br />" . $loc['city'] . ", " . $loc['state'] . ' ' . $loc['zipcode'];
			
			$data = array(
					'title' => html_entity_decode($loc['title']),
					'address' => $js_address, 
					'street_address' => $loc['street_address'], 
					'street_address_line_2' => $loc['street_address_line_2'], 
					'city' => $loc['city'], 
					'state' => $loc['state'], 
					'zipcode' => $loc['zipcode'], 
					'distance' => !empty($loc['distance']) ? $loc['distance'] : '', 
					'phone' => $loc['phone'], 
					'lat' => $loc['lat'], 
					'lng' => $loc['lng'],
					'ID' => $loc['ID'],
					'permalink' => get_permalink($loc['ID'])
			);

			$showEmail = get_option('loc_p_show_email', true);
			if ($showEmail && isset($loc['email']) && strlen($loc['email']) > 0) {
				$data['email'] = $loc['email'];
			}

			$showFax = get_option('loc_p_show_fax_number', true);		
			if ($showFax && isset($loc['fax']) && strlen($loc['fax']) > 0) {
				$data['fax'] = $loc['fax'];
			}
			
			$showDirectionLinks = get_option('loc_p_show_directions_links', true);
			if ( $showDirectionLinks ) {
				$data['directions_url'] = $this->get_directions_url($loc);
				$data['directions_label'] = get_option('loc_p_directions_links_label', '[Directions]');
			}

			return $data;
		}
			
				function get_google_maps_info_window_template()
		{
			return $this->root->get_template_content('google-maps-info-window.php');
		}
		
		function get_search_radius_in_miles($radius = false)
		{
			$m_or_km = get_option('loc_p_miles_or_km', 'miles');
			
			//if no radius is passed, load radius from options page
			if(!$radius) {
				$radius = intval(get_option('loc_p_search_radius', 0));
			}
			if ($radius < 1) { 
				$radius = 50;
			} else if ($radius > 500) { 
				$radius = 500;
			}
			if ($m_or_km == 'km') { // convert kilometers to miles if needed
				return ($radius / .621371);
			} else {
				return $radius;
			}
		}		
			
		function get_directions_url($loc)
		{
			$encoded_addr = urlencode( $this->build_address_string($loc) );
			$directions_url = sprintf('https://www.google.com/maps/dir/Current+Location/%s', $encoded_addr);
			return apply_filters('locations_directions_url', $directions_url);
		}

		function build_directions_link($loc)
		{
			$directions_url = $this->get_directions_url($loc);
			$directions_label = get_option('loc_p_directions_links_label', '[Directions]');
			$directions_link = sprintf( ' <a href="%s" class="directions_link" target="_blank">%s</a>',
										esc_url( $directions_url ),
										wp_kses( $directions_label, 'strip' ) );
			return $directions_link;
		}

		
		function build_address_string($loc)
		{
			// build the address string
			$address = '';
			$address .= $loc['street_address'];
			if (strlen($loc['street_address_line_2']) > 0) {
				$address .= ' ' . $loc['street_address_line_2'];
			}
			$address .= ' ';
			$address .= $loc['city'];
			$address .= ', ';
			$address .= $loc['state'];
			$address .= ' ';
			$address .= $loc['zipcode'];
			return $address;
		}
		
		function geolocate_current_visitor($ignore_cache = false)
		{
			$ip = $this->Utils->get_real_user_ip();
			$cache_key = 'locations_geoloc_' . md5($ip);
			if ( !$ignore_cache && ($geo = get_transient($cache_key) !== FALSE) ) {
				return $geo;
			}
			else {
				if (is_ssl()) {
					$geolocator_url = 'https://freegeoip.net/json/' . $ip;
				} else {
					$geolocator_url = 'http://freegeoip.net/json/' . $ip;
				}
				$url_contents = wp_remote_get( $geolocator_url );
				if (! is_wp_error( $url_contents ) && is_array( $url_contents ) && isset($url_contents['body']) && strlen($url_contents['body']) > 0) {
					$response_body = $url_contents['body'];
					$geo_json = json_decode($response_body);
					$geo = array(
						'ip' => sanitize_text_field($geo_json->ip),
						'country_code' => sanitize_text_field($geo_json->country_code),
						'country_name' => sanitize_text_field($geo_json->country_name),
						'region_name' => sanitize_text_field($geo_json->region_name),
						'state' => sanitize_text_field($geo_json->region_name),
						'city' => sanitize_text_field($geo_json->city),
						'latitude' => sanitize_text_field($geo_json->latitude),
						'longitude' => sanitize_text_field($geo_json->longitude),
						'friendly_location' => sanitize_text_field($geo_json->country_name),
					);
					// if US, replace country name with city and state
					if ($geo['country_code'] == 'US') {
						$geo['friendly_location'] = sanitize_text_field($geo_json->city) . ', ' . sanitize_text_field($geo_json->region_name) . ', USA';
					}
					// cache result indefinitely (1 year)
					set_transient( $cache_key, $geo, 31536000 );
					return $geo;
				}
				else {
					return false;
				}
			}
		}
		
		
	}
