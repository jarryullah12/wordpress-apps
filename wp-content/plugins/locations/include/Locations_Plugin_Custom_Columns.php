<?php
	class Locations_Plugin_Custom_Columns
	{
		var $root;
		
		function __construct($root)
		{
			$this->root = $root;
			$this->add_hooks();
		}
		
		function add_hooks()
		{
			if ( is_admin() ) {
				// setup custom columns on View All Locations page
				add_filter( 'manage_location_posts_columns' , array($this, 'update_locations_list_columns') );
				add_action( 'manage_location_posts_custom_column' , array($this, 'output_locations_column_content'), 10, 2 );
				add_filter( 'manage_edit-location_sortable_columns', array($this, 'make_custom_columns_sortable') );
				add_action( 'pre_get_posts', array($this, 'enable_orderby_for_custom_columns') );
				add_filter( 'default_hidden_columns', array($this, 'set_default_hidden_columns'), 10, 2 );
			}
		}

		/* 
		 * Register our custom columns with WordPress
		 *
		 * Called by WP's manage_location_posts_columns hook
		 *
		 * @param array $columns The current list of columns
		 * @return array The updated list of columns, with our custom columns added
		 */
		function update_locations_list_columns($columns)
		{
			// now add the rest of the columns
			$new_cols = array(
				'mailing_address' => __('Mailing Address'),
				'street_address' => __('Street Address'),
				'city' => __('City'),
				'state' => __('State'),
				'zipcode' => __('Postal Code'),
				'country' => __('Country'),
				'latitude' => __('Latitude'),
				'longitude' => __('Longitude'),
				'single_shortcode' => __('Shortcode'),
			);
			// insert our new cols between the 2nd (Title) and 3rd (Categories) cols
			$columns = array_slice($columns, 0, 2, true) +
					   $new_cols +
					   array_slice($columns, 2, count($columns)-2, true);
			
			return $columns;
		}
		
		/* 
		 * Output the content for our custom columns
		 *
		 * Called by WP's output_locations_column_content hook
		 *
		 * @param string $column The key of the column to output
		 * @param int The ID of the location (post) in the database
		 */
		function output_locations_column_content($column, $location_id)
		{
			 switch ( $column )
			 {
				case 'street_address' :
					$addr = get_post_meta( $location_id , '_ikcf_street_address' , true ); 
					$addr2 = get_post_meta( $location_id , '_ikcf_street_address_line_2' , true );
					echo !empty($addr2)
						 ? sprintf('%s<br>%s', $addr, $addr2)
						 : $addr;
					break;

				case 'city' :
				case 'state' :
				case 'zipcode' :
				case 'country' :
				case 'phone' :
				case 'website' :
				case 'email' :
				case 'fax' :
				case 'info_box' :
				case 'longitude' :
				case 'latitude' :
					$meta_key = sprintf( '_ikcf_%s', $column );
					echo wp_kses( get_post_meta( $location_id , $meta_key , true ), 'post' ); 					 
					break;
					
				case 'mailing_address' :
					$loc = get_post($location_id);
					if ( empty($loc) ) {
						break;					
					}
					echo wp_kses( get_the_title($location_id), 'strip' ) . '<br>';
					$this->root->output_address_html(
						get_post_meta( $location_id, '_ikcf_street_address', true ),
						get_post_meta( $location_id, '_ikcf_street_address_line_2', true ),
						get_post_meta( $location_id, '_ikcf_city', true ),
						get_post_meta( $location_id, '_ikcf_state', true ),
						get_post_meta( $location_id, '_ikcf_zipcode', true )
					);
					break;
				
				case 'single_shortcode' :
					$tmpl = '<input type="text" value="[locations id=\'%d\']" class="shortcode_to_copy" style="max-width:100%%" />';
					printf($tmpl, $location_id);
					break;

				default:
					break;
			 }
		}

		/* 
		 * Tell WordPress that some of our custom columns are sortable
		 *
		 * Called by WP's manage_edit-location_sortable_columns hook
		 *
		 * @param array $columns The list of currently sortable columns
		 * @return array The list of sortable columns with our columns added
		 */
		function make_custom_columns_sortable( $columns )
		{
			$columns['street_address'] = 'locations_street_address';
			$columns['street_address_line_2'] = 'locations_street_address';
			$columns['latitude'] = 'locations_latitude';
			$columns['longitude'] = 'locations_longitude';
			$columns['city'] = 'locations_city';
			$columns['state'] = 'locations_state';
			$columns['zipcode'] = 'locations_zipcode';
			$columns['country'] = 'locations_country';
			return $columns;
		}
		
		/* 
		 * Teaches WordPress how to convert our custom keys into an orderby clause
		 *
		 * Called by WP's pre_get_posts hook
		 *
		 * @param WP_Query $query 
		 */
		function enable_orderby_for_custom_columns( $query )
		{
			if( ! is_admin() ) {
				return;
			}
			
			$orderby = $query->get( 'orderby');	
			
			// make sure the column name begins with our prefix, 'locations_'
			if ( strpos($orderby, 'locations_') !== 0 ) {
				return;
			}
			
			// strip off the prefix and continue
			$orderby = substr( $orderby, 10);
			if ( $this->is_locations_custom_column($orderby) ) {
				$meta_key = sprintf('_ikcf_%s', $orderby);
				$query->set('meta_key', $meta_key);
				$query->set('orderby','meta_value'); // sort alphabetically
			}
		}
		
		/* 
		 * Tells whether the given key is one of our custom column keys
		 * 
		 * @param string $key the key name to check
		 * 
		 * @return bool, true if its one of our columns, false if not
		 */
		function is_locations_custom_column($key)
		{
			$location_cols = array( 
				'street_address',
				'street_address_line_2',
				'city',
				'state',
				'zipcode',
				'country',
				'phone',
				'website',
				'email',
				'fax',
				'info_box',
				'longitude',
				'latitude',	
				'single_shortcode'
			);
			return in_array($key, $location_cols);
		}
		
		/* 
		 * Hides some of the custom columns on Locations by default
		 * Once the user interacts with the screen options, their preferences will 
		 * be automatically saved by WP, and these defaults will be overriden.
		 *
		 * Called by WP's default_hidden_columns hook
		 *
		 * @param array $hidden List of currently hidden columns
		 * @param object $screen WordPress screen object for the current screen
		 */
		function set_default_hidden_columns( $hidden, $screen )
		{
			// We only want to modify the edit.php?post_type=location screen, so
			// if this is another screen quit now
			if ( empty($screen->id) || $screen->id !== 'edit-location' ) {
				return $hidden;
			}
			
			// init empty array if needed
			if ( !is_array($hidden) || empty($hidden) ) {
				$hidden = array();
			}
			
			// add our hidden-by-default columns to the list
			$hidden[] = 'country';
			$hidden[] = 'longitude';
			$hidden[] = 'latitude';
			$hidden[] = 'mailing_address';		
			return $hidden;		
		}
		
	}	