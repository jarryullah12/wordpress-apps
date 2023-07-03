<?php
/*
Plugin Name: Locations: Store Locator Plugin
Plugin Script: locations.php
Plugin URI: http://goldplugins.com/our-plugins/locations/
Description: Easily add a Store Locator to your website. List your business' locations and show a Google map for each one.
Version: 4.0
Author: Gold Plugins
Author URI: http://goldplugins.com/

This file is part of Locations.

Locations is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Locations is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Locations.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('gold-framework/plugin-base.php');
require_once('gold-framework/loc_p_kg.php');
require_once('include/Locations_Plugin_DB.php');
require_once('include/Locations_Plugin_Custom_Columns.php');
require_once('include/Locations_Plugin_Geocoder.php');
require_once('include/Locations_Plugin_Utils.php');
require_once('include/Locations_Plugin_Store_Locator.php');
require_once('include/Locations_Plugin_Settings.php');
require_once('include/Locations_Plugin_Notices.php');
require_once('include/Locations_Upgrade_Reminder.class.php');
require_once("lib/GP_Media_Button/gold-plugins-media-button.class.php");
require_once("lib/GP_Janus/gp-janus.class.php");
require_once('lib/GP_Sajak/gp_sajak.class.php');
require_once('lib/GP_MegaSeptember/mega.september.class.php');
require_once('lib/GP_Aloha/gp_aloha.class.php');
require_once('widgets/single_location_widget.php');
require_once('widgets/locations_list_widget.php');
require_once('widgets/store_locator_widget.php');
require_once('widgets/store_locator_widget_ajax.php');

/* Gutenburg blocks */
if ( function_exists('register_block_type') ) {
	require_once('blocks/all-locations.php');
	require_once('blocks/search-locations.php');
	require_once('blocks/single-location.php');
	require_once('blocks/store-locator.php');
}

class LocationsPlugin extends GoldPlugin
{
	var $google_geocoder_api_key = '';
	
	function __construct()
	{
		$this->create_post_types();
		$this->register_taxonomies();
		$this->add_stylesheets_and_scripts();				
		$this->set_google_maps_api_key();	
		$this->DB = new Locations_Plugin_DB();
		$this->Custom_Columns = new Locations_Plugin_Custom_Columns($this);
		$this->Geocoder = new Locations_Plugin_Geocoder($this->google_geocoder_api_key);
		$this->Settings = new Locations_Plugin_Settings( __FILE__, $this->isValidKey() );
		$this->Utils = new Locations_Plugin_Utils();
		$this->Notices = new Locations_Plugin_Notices( $this->Geocoder );
		$this->Store_Locator = new Locations_Plugin_Store_Locator( $this, $this->DB, $this->Geocoder, $this->Utils );
		
		$this->add_hooks();
		
		$this->register_blocks();
		
		// add media buttons to admin
		$cur_post_type = ( isset($_GET['post']) ? get_post_type(intval($_GET['post'])) : '' );
		if( is_admin() && ( empty($_REQUEST['post_type']) || $_REQUEST['post_type'] !== 'location' ) && ($cur_post_type !== 'location') ) {
			add_action('admin_init', array($this, 'add_media_buttons'));
		}
		
		// load Janus
		if (class_exists('GP_Janus')) {
			$locations_Janus = new GP_Janus();
		}

		//flush rewrite rules - only do this once!
		add_action( 'locations_flush_rewrite_rules', array($this, 'flush_location_rewrite_rules') );
		register_activation_hook( __FILE__, array($this, 'activation_hook') );	
						
		if ( is_admin() ) {
			// load Aloha
			$config = array(
				'menu_label' => __('About Plugin'),
				'page_title' => __('Welcome To Locations'),
				'tagline' => __('Locations is the easiest way to add a Store Locator to your website.'),
				'top_level_menu' => 'edit.php?post_type=location',
				'menu_page' => 'gp_locations_aloha',
				'meta_key' => '_gp_locations_aloha_show_welcome_on_next_page_load',
				'welcome_page_url' => admin_url('edit.php?post_type=location&page=gp_locations_aloha'),
			);
			$this->Aloha = new GP_Aloha($config);
			add_filter( 'gp_aloha_welcome_page_content_edit.php?post_type=location', array($this, 'get_welcome_template') );
		}
		
		if ( !$this->isValidKey() ) {
			$this->Upgrade_Reminder = new Locations_Upgrade_Reminder();
		}
		
		add_action( 'admin_init', array($this, 'add_extra_classes_to_admin_menu') );				
		parent::__construct(); 
	}
	
	//only do this once
	function activation_hook()
	{
		$this->flush_location_rewrite_rules();
		
		// make sure the welcome screen gets seen again
		$this->Aloha->reset_welcome_screen();
	}	
	
	function get_welcome_template()
	{
		$base_path = plugin_dir_path( __FILE__ );
		$template_path = $base_path . 'assets/content/welcome.php';
		$is_pro = $this->isValidKey();
		$content = file_exists($template_path)
				   ? include($template_path)
				   : '';
		return $content;
	}
	
	function add_hooks()
	{
		if ( !empty($this->_hooks_added) ) {
			return;
		}
		$this->_hooks_added = true;
		
		/* Remove unneeded meta boxes from the Locations custom post type */
		add_action('init', array($this, 'remove_features_from_locations'));
		
		/* Create the shortcodes */
		add_shortcode('locations', array($this, 'locations_shortcode'));
		
		/* Create the widgets */
		add_action( 'widgets_init', array($this, 'register_widgets' ));

		/* Enable custom templates (currently only available for single locations) */
		add_filter('the_content', array($this, 'single_location_content_filter'));
		
		// add vcard classes to single location pages
		add_filter( 'post_class', array($this, 'add_vcard_post_class') );
		add_filter( 'the_title', array($this, 'add_vcard_title_class') );

		//add Settings & Upgrade links to the plugin list
		add_filter( 'plugin_row_meta', array($this, 'add_custom_links_to_plugin_description'), 10, 2 );	
		
		// add our custom meta boxes
		add_action( 'admin_menu', array($this, 'add_meta_boxes'));
		
		// add hooks to handle AJAX searches from the store locators
		add_action( 'wp_ajax_locations_pro_ajax_search', array($this->Store_Locator, 'handle_ajax_searches') );
		add_action( 'wp_ajax_nopriv_locations_pro_ajax_search', array($this->Store_Locator, 'handle_ajax_searches') );		
		
		/* Add any hooks that the base class has setup */
		parent::add_hooks();
		
		// add Gutenburg custom blocks category 
		add_filter( 'block_categories', array($this, 'add_gutenburg_block_category'), 10, 2 );
		
		// make the list of themes available in JS (admin only)
		add_action( 'admin_init', array($this, 'provide_config_data_to_admin') );		
	}
	
	function register_blocks()
	{
		/* Register custom blocks */
		if ( function_exists('register_block_type') ) {
			
			register_block_type( 'locations/single-location', array(
				'editor_script' 	=> 'single-location-block-editor',
				'editor_style'  	=> 'single-location-block-editor',
				'style' 		    => 'single-location-block',
				'render_callback' 	=> array($this, 'locations_shortcode')
			) );

			register_block_type( 'locations/all-locations', array(
				'editor_script' => 'all-locations-block-editor',
				'editor_style'  => 'all-locations-block-editor',
				'style'         => 'all-locations-block',
				'render_callback' 	=> array($this, 'locations_shortcode')
			) );		

			register_block_type( 'locations/store-locator', array(
				'editor_script' => 'store-locator-block-editor',
				'editor_style'  => 'store-locator-block-editor',
				'style'         => 'store-locator-block',
				'render_callback' 	=> array($this->Store_Locator, 'store_locator_shortcode')
			) );			

			register_block_type( 'locations/search-locations', array(
				'editor_script' => 'search-locations-block-editor',
				'editor_style'  => 'search-locations-block-editor',
				'style'         => 'search-locations-block',
				'render_callback' 	=> array($this->Store_Locator, 'store_locator_shortcode')
			) );
		}
	}
	
	function add_media_buttons()
	{
		global $Locations_MediaButton;
		$media_buttons = array();
		$media_buttons[] = array(
			'label' => 'Single Location',
			'shortcode' => 'locations',
			'widget_class' => 'singlelocationwidget',
			'icon' => 'location-alt'
		);
		$media_buttons[] = array(
			'label' => 'List of Locations',
			'shortcode' => 'locations',
			'widget_class' => 'locationslistwidget',
			'icon' => 'location-alt'
		);
		$media_buttons[] = array(
			'label' => 'Store Locator',
			'shortcode' => 'store_locator',
			'widget_class' => 'storelocatorwidgetajax',
			'icon' => 'location-alt'
		);
		$media_buttons[] = array(
			'label' => 'Search Locations',
			'shortcode' => 'store_locator',
			'widget_class' => 'storelocatorwidget',
			'icon' => 'location-alt'
		);
		$media_buttons = apply_filters('locations_admin_media_buttons', $media_buttons);
		
		$Locations_MediaButton = new Gold_Plugins_Media_Button('Locations', 'location-alt');
		foreach( $media_buttons as $btn ) {
			$Locations_MediaButton->add_button($btn['label'], $btn['shortcode'], $btn['widget_class'], $btn['icon']);
		}
	}

	// add inline links to our plugin's description area on the Plugins page
	function add_custom_links_to_plugin_description($links, $file) {

		/** Get the plugin file name for reference */
		$plugin_file = plugin_basename( __FILE__ );
	 
		/** Check if $plugin_file matches the passed $file name */
		if ( $file == $plugin_file )
		{
			$new_links['settings_link'] = '<a href="edit.php?post_type=location&page=locations-settings">Settings</a>';
			$new_links['support_link'] = '<a href="https://goldplugins.com/contact/?utm-source=plugin_menu&utm_campaign=support&utm_banner=locations-plugin-menu" target="_blank">Get Support</a>';
				
			if(!$this->isValidKey()) {
				$new_links['upgrade_to_pro'] = '<a href="https://goldplugins.com/our-plugins/locations/upgrade-to-locations-pro/?utm_source=plugin_menu&utm_campaign=upgrade" target="_blank">Upgrade to Pro</a>';
			}
			
			$links = array_merge( $links, $new_links);
		}
		return $links; 
	}
	
	//add meta box for single location shortcode
	function add_meta_boxes()
	{
		add_meta_box( 'single_location_shortcode', 'Shortcodes', array($this,'display_shortcodes_meta_box'), 'location', 'side', 'default' );
	}
	
	// Displays a meta box with the shortcodes to display the current location
	function display_shortcodes_meta_box() {
		global $post;
		echo "Add this shortcode to any page where you'd like to <strong>display</strong> this Location:<br />";
		printf( '<textarea>[locations id="%s"]</textarea>',
				intval($post->ID) );
	}//add Custom CSS
	
	function register_widgets()
	{
		register_widget( 'singleLocationWidget' );
		register_widget( 'singleLocationWidget' );
		register_widget( 'locationsListWidget' );
		register_widget( 'storeLocatorWidget' );
		register_widget( 'storeLocatorWidgetAJAX' );
	}
	
	function single_location_content_filter($content)
	{
		if ( is_single() && get_post_type() == 'location' ) {
			global $location_data;
			$location_data = $this->get_location_data_for_post();
			$template_content = $this->get_template_content('single-location-content.php');
			return $template_content;
		}
		return $content;
	}
	
	/* Creates the Locations custom post type */
	function create_post_types()
	{
		$single_view_slug_option = get_option('loc_p_single_view_slug', 'locations');
		
		//optional definable single view slug
		//defaults to locations
		$single_view_slug = !empty($single_view_slug_option) ? $single_view_slug_option : 'locations';
		
		$postType = array(
			'name' => 'Location', 
			'plural' => 'Locations', 
			'slug' => $single_view_slug, 
			'menu_icon' => 'dashicons-location-alt',
			'post_type_name' => 'location'
			
		);
		
		$customFields = array();
		//$customFields[] = array('name' => 'name', 'title' => 'Name', 'description' => 'Name of this location', 'type' => 'text');	
		$customFields[] = array('name' => 'street_address', 'title' => 'Street Address', 'description' => 'Example: 127 North St.', 'type' => 'text');	
		$customFields[] = array('name' => 'street_address_line_2', 'title' => 'Street Address (line 2)', 'description' => 'Example: Suite 420', 'type' => 'text');	
		$customFields[] = array('name' => 'city', 'title' => 'City', 'description' => 'Example: Carrington', 'type' => 'text');
		$customFields[] = array('name' => 'state', 'title' => 'State', 'description' => 'Example: NC', 'type' => 'text');
		$customFields[] = array('name' => 'country', 'title' => 'Country', 'description' => 'Example: United States', 'type' => 'text'); // TODO: propagate this field into addresses, etc
		$customFields[] = array('name' => 'zipcode', 'title' => 'Zipcode', 'description' => 'Example: 27601', 'type' => 'text');
		$customFields[] = array('name' => 'phone', 'title' => 'Phone', 'description' => 'Primary phone number of this location, example: 919-555-3333', 'type' => 'text');
		$customFields[] = array('name' => 'website_url', 'title' => 'Website', 'description' => 'Website URL address for this location, example: http://goldplugins.com', 'type' => 'text');

		$showEmail = get_option('loc_p_show_email', true);
		if ($showEmail) {
			$customFields[] = array('name' => 'email', 'title' => 'Email', 'description' => 'Email address for this location, example: shopname@ourbrand.com', 'type' => 'text');
		}

		$showFax = get_option('loc_p_show_fax_number', true);		
		if ($showFax) {
			$customFields[] = array('name' => 'fax', 'title' => 'Fax', 'description' => 'Fax number of this location, example: 919-555-3344', 'type' => 'text');
		}
		
		$showInfo = get_option('loc_p_show_info', true);
		if ($showInfo) {
			$customFields[] = array('name' => 'info_box', 'title' => 'Additional Info', 'description' => 'Free form text area for Additional Info, such as Hours of Operation.  HTML is allowed.', 'type' => 'textarea');
		}
		
		$customFields[] = array('name' => 'show_map', 'title' => 'Show Google Map', 'description' => 'If checked, a Google Map with a marker at the above address will be displayed.', 'type' => 'checkbox');
		$customFields[] = array('name' => 'latitude', 'title' => 'Latitude', 'description' => 'Latitude of this location. You can leave this blank, and we will calculate it for you based on the address you entered (with the Google Maps geocoder).', 'type' => 'text');
		$customFields[] = array('name' => 'longitude', 'title' => 'Longitude', 'description' => 'Longitude of this location. You can ignore this field, and we will calculate it for you based on the address you entered (with the Google Maps geocoder).', 'type' => 'text');

		$this->add_custom_post_type($postType, $customFields);
		
		// add a hook to geocode addresses if needed, which runs *after* we have already saved the custom fields for this location
		add_action( 'save_post', array( &$this, 'geocode_post_on_save' ), 8, 2 );
		
	}
	
	function register_taxonomies()
	{
		$this->add_taxonomy('location-categories', 'location', 'Location Category', 'Location Categories');
	}
	
	/* Load the Google Maps API Key from the plugin settings into a member variable. Called on init. */
	function set_google_maps_api_key()
	{
		$this->google_geocoder_api_key = get_option('loc_p_google_maps_api_key', '');		
		// TODO: we should show a warning on the settings page if this has not been set
	}	
	
	/* Automatically geocodes the addresses of new Locations (uses Google Maps geocoder) */
	function geocode_post_on_save( $post_id, $post )
	{
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
			
		$myLat = get_post_meta($post_id, '_ikcf_latitude', true);
		$myLon = get_post_meta($post_id, '_ikcf_longitude', true);
		$addrHash = get_post_meta($post_id, 'addr_hash', true);
			
		$myAddress = $this->get_address($post_id);
		$addressChanged = ( $addrHash != md5($myAddress) );

		// if either the latitude or the longitude is unknown, or the address has changed, we should geocode it now
		if ( $myLat == "" || $myLon == "" || $addressChanged )
		{
			// calculate the lat and lon based on the address provided
			$myCoordinates = $this->Geocoder->geocode_address($myAddress);
							
			// if the geocode worked, update the latitude and/or the longitude 
			// NOTE: we should only update previously empty values, so that the user can tweak them if needed
			if ($myCoordinates !== FALSE)
			{
				// if latitude was unknown or the address changed, we should update it now
				if ($myLat == "" || $addressChanged) {
					update_post_meta($post_id, '_ikcf_latitude', $myCoordinates['lat']);
				}
					
				// if longitude was unknown or the address changed, we should update it now
				if ($myLon == "" || $addressChanged) {
					update_post_meta($post_id, '_ikcf_longitude', $myCoordinates['lng']);
				}
				
				// update the address hash meta, so we don't geocode again on the next save
				update_post_meta($post_id, 'addr_hash', md5($myAddress));
			}
		}
	}
	
	/* Disables some of the normal WordPress features on the Location custom post type (the editor, author, comments, excerpt) */
	function remove_features_from_locations()
	{
		remove_post_type_support( 'location', 'editor' );
		remove_post_type_support( 'location', 'excerpt' );
		remove_post_type_support( 'location', 'comments' );
		remove_post_type_support( 'location', 'author' );
	}
	
	// have to enqueue built in scripts on wp_enqueue_scripts
	function locations_add_script()
	{		
		$gmapsUrl = !empty($this->google_geocoder_api_key)
					? '//maps.google.com/maps/api/js?key=' . $this->google_geocoder_api_key . '&sensor=false'
					: '//maps.google.com/maps/api/js?sensor=false';
		$jsUrl = plugins_url( 'assets/js/locations.js' , __FILE__ );
		
		wp_enqueue_script(
			'gmaps-js',
			$gmapsUrl,
			array( 'jquery' ),
			false,
			true
		); 
		

		wp_register_script(
			'locations-js',
			$jsUrl,
			array( 'jquery' ),
			'1.9',
			true
		);   
		// Localize the script with new data
		$translation_array = array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		);
		wp_localize_script( 'locations-js', 'locations_pro_store_locator_settings', $translation_array );
		wp_enqueue_script( 'locations-js' );
	}
	
	/* Enqueue our CSS files and Javascripts. Adds jQuery and Google Maps as well. */
	function add_stylesheets_and_scripts()
	{
		$cssUrl = plugins_url( 'assets/css/locations.css' , __FILE__ );
		$this->add_stylesheet('wp-locations-css',  $cssUrl);
				
		if (is_admin()) {
			//add admin css
			add_action( 'admin_enqueue_scripts', array($this, 'locations_add_admin_css' ));
		}
				
		//add JS
		add_action( 'wp_enqueue_scripts', array($this, 'locations_add_script' ));
	}
	
	
	/* Enqueue Admin CSS */
	function locations_add_admin_css($hook)
	{
		//RWG: only enqueue scripts and styles on Locations admin pages or widgets page
		$screen = get_current_screen();
		
		if ( strpos($hook,'locations') !== false || $screen->id === "widgets" || is_customize_preview() ) {
			$adminCssUrl = plugins_url( 'assets/css/admin_style.css' , __FILE__ );
			wp_register_style( 'wp-locations-admin-css', $adminCssUrl );
			wp_enqueue_style( 'wp-locations-admin-css' );
			
			wp_enqueue_script(
				'gp-admin_v2',
				plugins_url('assets/js/gp-admin_v2.js', __FILE__),
				array( 'jquery' ),
				false,
				true
			);	
		}
	}

	/* Shortcodes */
	
	function get_template_content($template_name, $default_content = '')
	{	
		$template_path = $this->get_template_path($template_name);
		if (file_exists($template_path)) {
			// load template by including it in an output buffer, so that variables and PHP will be run
			ob_start();
			include($template_path);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		// couldn't find a matching template file, so return the default content instead
		return $default_content;
	}
	
	function get_template_path($template_name)
	{
		// checks if the file exists in the theme first,
		// otherwise serve the file from the plugin
		if ( $theme_file = locate_template( array ( $template_name ) ) ) {
			$template_path = $theme_file;
		} else {
			$template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
		}
		return $template_path;
	}
	
	/* output a list of all locations */
	function locations_shortcode($atts, $content = '')
	{
		// merge any settings specified by the shortcode with our defaults
		$defaults = array(	'caption' => '',
							'style' =>	'small',
							'show_photos' => 'true',
							'id' => '',
							'category' => '',
							'show_email' => get_option('loc_p_show_email', true),
							'show_fax' => get_option('loc_p_show_fax_number', true),
							'show_info' => get_option('loc_p_show_info', true),
							'show_map' => get_option('loc_p_show_map', 'per_location'),//always, never (or 0), per location
							'show_phone' => 1,
							'order' => 'ASC',
							'orderby' => 'title',
							'force_single_mode' => false,
						);
						
		$atts = shortcode_atts($defaults, $atts);
		
		if( !is_numeric($atts['id']) && !$atts['force_single_mode'] ) {
			// get a list of all the locations			
			$all_locations = $this->DB->get_all_locations($atts);
		}
		else if ( !is_numeric($atts['id']) && $atts['force_single_mode'] ) {
			// no ID was specified but force_single_mode is true (i.e., this was
			// rendered by a Single Location custom block), so render nothing.
			return '';
		}
		else {
			// a specific location was specified, so just render it alone
			$all_locations = $this->DB->get_single_location($atts['id']);
		}
		
		// start the HTML output with a wrapper div
		$html = '<div class="locations">';

		// add the caption, if one was specified
		if (strlen($atts['caption']) > 1) {
			$html .= '<h2 class="locations-caption">' . htmlentities($atts['caption']) . '</h2>';
		}

		// loop through the locations, and add the generated HTML for each location
		if ($all_locations && count($all_locations) > 0)
		{		
			foreach ($all_locations as $loc) {			
				$html .= $this->build_location_html($loc, $atts);
			}
		}
		
		// close the locations div and return the finished HTML
		$html .= '</div>'; // <!--.locations-->
		return $html;
	}
	
	// generates the HTML for a single location. 
	// NOTE: this is a helper function for the locations_shortcode function
	private function build_location_html( $loc, $atts = array() )
	{
		// load the meta data for this location (name, address, zipcode, etc)
		$title = $loc->post_title;
		$street_address = $this->get_option_value($loc->ID, 'street_address','');
		$street_address_line_2 = $this->get_option_value($loc->ID, 'street_address_line_2','');
		$city = $this->get_option_value($loc->ID, 'city','');
		$state = $this->get_option_value($loc->ID, 'state','');
		$zipcode = $this->get_option_value($loc->ID, 'zipcode','');
		$phone = $this->get_option_value($loc->ID, 'phone','');
		$fax = $this->get_option_value($loc->ID, 'fax','');
		$email = $this->get_option_value($loc->ID, 'email','');
		$info_box = $this->get_option_value($loc->ID, 'info_box','');
		$website_url = $this->get_option_value($loc->ID, 'website_url','');
		$showPhone = isset($atts['show_phone']) ? $atts['show_phone'] : 1;
		
		//RWG: these can be passed in via attributes or loaded via the options panel
		$showEmail = isset($atts['show_email']) ? $atts['show_email'] : get_option('loc_p_show_email', true);
		$showFax =isset($atts['show_fax']) ? $atts['show_fax'] : get_option('loc_p_show_fax_number', true);
		$showInfo = isset($atts['show_info']) ? $atts['show_info'] : get_option('loc_p_show_info', true);				
		
		// load any needed atts that came from the shortcode
		$show_photo = isset($atts['show_photos']) ? $atts['show_photos'] : 'true';
		
		//RWG: this can be passed in via attributes or loaded via the options panel
		$show_map = isset($atts['show_map']) ? $atts['show_map'] : get_option('loc_p_show_map', 'per_location');
		if (!$show_map) {
			//map being hidden via shortcode
			$add_map = false;
		} else if ($show_map == 'always') {
			$add_map = true;
		} else if ($show_map == 'never') {
			$add_map = false;
		} else { // per location
			$add_map = $this->get_option_value($loc->ID, 'show_map', false);
		}
		
		// start building the HTML for this location
		$html = '';
		
		// add the featured image, if one was specified and show photos is true
		$img_html = '';
		if($show_photo) {
			$img_html = $this->build_featured_image_html($loc);
		}
		$hasPhoto = (strlen($img_html) > 0);
				
		// start the location div. Add the hasPhoto or noPhoto class, depending on whether a featured image was specified
		$html .= '<div class="location ' . ($hasPhoto ? 'hasPhoto' : 'noPhoto') . '">';
		$html .= $img_html; // $img_html may be empty
						
		// add the location's title
		$html .= '<h3>' . htmlentities($title) . '</h3>';
			
		// add the address, with each part wrapped in its own HTML tag
		$html .= $this->build_address_html($street_address, $street_address_line_2, $city, $state, $zipcode);
		
		// add distance if it exists
		if ( !empty($loc->distance) ) {
			$miles_or_km = ( get_option('loc_p_miles_or_km', 'miles') == 'miles' ) ? 'miles' : 'kilometers';
			$html .= '<div class="distance-wrapper"><em>' . htmlentities($loc->distance) . ' ' . $miles_or_km . ' away</em></div>';
		}

		// add the phone number and fax (if specified)
		if (strlen($phone) > 1 && $showPhone) {
			$html .= '<div class="phone-wrapper"><strong>Phone:</strong> <span class="num phone">' . htmlentities($phone) . '</span></div>';
		}
		if (strlen($fax) > 1 && $showFax) {
			$html .= '<div class="fax-wrapper"><strong>Fax:</strong> <span class="num fax">' . htmlentities($fax) . '</span></div>';
		}	
		if (strlen($info_box) > 1 && $showInfo) {
			$html .= '<div class="info-wrapper">' . $info_box . '</div>';
		}			
		
		// add links for Map, Directions, Email, and Website
		$html .= $this->build_links_html($street_address, $street_address_line_2, $city, $state, $zipcode, $email, $website_url, $add_map);
		
		if($add_map) {
			$address = htmlentities($street_address . ", " . $street_address_line_2 . ", " . $city . ", " . $state . ", " . $zipcode);
			$map_width = !empty( $atts['map_width'] ) ? $atts['map_width'] : '425px';
			$html .= sprintf( '<div class="locations_gmap"><iframe width="%s" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $address . '&amp;t=h&amp;ie=UTF8&amp;hq=&amp;hnear=' . $address . '&amp;z=14&amp;output=embed"></iframe></div>', $map_width );
		}
		
		// close the location div and return the finished HTML
		$html .= '</div>'; // <!--.location-->
		
		// Allow the user to modify this location's HTML with a pair of filters
		// One is applied to all locations, one is applied specifically to this ID
		$html = apply_filters('locations_single_location_html', $html, $loc, $atts);
		$html = apply_filters('locations_single_location_html_' . $loc->ID, $html, $loc, $atts);
		
		return $html;
	}
	
	private function build_featured_image_html($loc)
	{
		$img_html = '';
		$post_thumbnail_id = get_post_thumbnail_id( $loc->ID );
		if ($post_thumbnail_id !== '' && $post_thumbnail_id > 0)
		{
			$hasPhoto = true;
			$img_src = wp_get_attachment_image_src($post_thumbnail_id, 'medium');
			$banner_style = "background-image: url('" . $img_src[0] . "');";
			$img_html .= '<div class="location-photo" style="' . $banner_style . '">';
			$img_html .= '</div>'; // <!--.location-photo-->
		}
		
		return $img_html;
	
	}
	
	function output_address_html($street_address, $street_address_line_2, $city, $state, $zipcode)
	{
		print('<div class="address">');
		printf( '<div class="addr">%s</div>', htmlentities($street_address) );
		if ( !empty($street_address_line_2) ) {
			printf( '<div class="addr2">%s</div>', htmlentities($street_address_line_2) );
		}
		print( '<div class="city_state_zip">' );
		printf( '<span class="city">%s</span>, <span class="state">%s</span> <span class="zipcode">%s</span>',
				htmlentities($city),
				htmlentities($state),
				htmlentities($zipcode) );
		print( '</div>' ); // <!--.city_state_zip-->
		print '</div>'; // <!--.address-->
	}
	
	function build_address_html($street_address, $street_address_line_2, $city, $state, $zipcode)
	{
		ob_start();
		$this->output_address_html($street_address, $street_address_line_2, $city, $state, $zipcode);
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	private function build_links_html($street_address, $street_address_line_2, $city, $state, $zipcode, $email = '', $website_url = '', $add_map = false)
	{
		$showEmail = get_option('loc_p_show_email', true);
	
		// generate the Google Maps links
		if (strlen($street_address_line_2) > 0) {
			$full_address = $street_address . ', ' . $street_address_line_2 . ' ' . $city . ', ' . $state . ' ' . $zipcode;
		} else {
			$full_address = $street_address . ' ' . $city . ', ' . $state . ' ' . $zipcode;		
		}
		$google_maps_url = 'https://maps.google.com/?q=' . urlencode($full_address);
		$google_maps_directions_url = 'https://maps.google.com/maps?saddr=current+location&daddr=' . urlencode($full_address);
		
		// generate the HTML for the actual links
		$html = '<div class="map_link">';
			if(!$add_map) {
				$html .= '<a href="' . $google_maps_url. '">Map</a>'; 
				$html .= ' <span class="divider">|</span> ';
			}
			$html .= '<a href="' . $google_maps_directions_url. '">Directions</a>';
			if (strlen($email) > 1 && $showEmail) {
				$html .= ' <span class="divider">|</span> ';
				$html .= '<a class="email" href="mailto:' . $email . '">Email</a>';
			}
			if (strlen($website_url) > 1) {
				$html .= ' <span class="divider">|</span> ';
				$html .= '<a class="website" href="' . $website_url . '">Website</a>';
			}
			if($add_map) {
				$html .= ' <span class="divider">|</span> ';
				$html .= '<a href="' . $google_maps_url. '">View Full Map</a>'; 
			}
		$html .= '</div>'; // <!--.map_link-->
		
		
		return $html;

	}
	
	/* Returns a formatted address for the specified Location */
	function get_address($post_id)
	{
		// load the required metadata
		$addr['street_address'] = $this->get_option_value($post_id, 'street_address','');
		$addr['street_address_line_2'] = $this->get_option_value($post_id, 'street_address_line_2','');
		$addr['city'] = $this->get_option_value($post_id, 'city','');
		$addr['state'] = $this->get_option_value($post_id, 'state','');
		$addr['zipcode'] = $this->get_option_value($post_id, 'zipcode','');
		
		// build the address string
		$address = '';
		$address .= $addr['street_address'];
		if (strlen($addr['street_address_line_2']) > 0) {
			$address .= ' ' . $addr['street_address_line_2'];
		}
		$address .= ' ';
		$address .= $addr['city'];
		$address .= ', ';
		$address .= $addr['state'];
		$address .= ' ';
		$address .= $addr['zipcode'];
		
		// return the completed string
		return $address;
	}
	
	/* Loads the meta data for a given location (name, address, zipcode, etc) and returns it as an array */
	function get_location_metadata($post_id)
	{
		$ret = array();
		$loc = get_post($post_id);
		$ret['ID'] = $loc->ID;
		$ret['title'] = $loc->post_title;
		$ret['street_address'] = $this->get_option_value($loc->ID, 'street_address','');
		$ret['street_address_line_2'] = $this->get_option_value($loc->ID, 'street_address_line_2','');
		$ret['city'] = $this->get_option_value($loc->ID, 'city','');
		$ret['state'] = $this->get_option_value($loc->ID, 'state','');
		$ret['zipcode'] = $this->get_option_value($loc->ID, 'zipcode','');
		$ret['phone'] = $this->get_option_value($loc->ID, 'phone','');
		$ret['fax'] = $this->get_option_value($loc->ID, 'fax','');
		$ret['email'] = $this->get_option_value($loc->ID, 'email','');
		$ret['website_url'] = $this->get_option_value($loc->ID, 'website_url','');
		$ret['lat'] = $this->get_option_value($loc->ID, 'latitude', '');
		$ret['lng'] = $this->get_option_value($loc->ID, 'longitude', '');
		
		// the show_map setting can be overriden on the settings panel, so we'll determine it now
		$show_map = get_option('loc_p_show_map', 'per_location');
		if ($show_map == 'always') {
			$ret['add_map'] = true;
		} else if ($show_map == 'never') {
			$ret['add_map'] = false;
		} else { // per location
			$ret['add_map'] = $this->get_option_value($loc->ID, 'show_map', false);
		}
		
		return $ret;
	}
	
	function get_location_data_for_post()
	{
		global $post;
		$location_data = $this->get_location_metadata($post->ID);
		
		//normalize some vars from "yes"/"no" and 1/0 to true/false
		$location_data['show_map'] = $this->Utils->normalize_truthy_value( $location_data['add_map'] );
		$location_data['show_email'] = $this->Utils->normalize_truthy_value( get_option('loc_p_show_email', true) );
		$location_data['show_fax'] = $this->Utils->normalize_truthy_value( get_option('loc_p_show_fax_number', true) );
		
		// add google maps URLs
		$full_address = $location_data['street_address'];
		if (!empty($location_data['street_address_line_2'])) {
			$full_address .= ', ' . $location_data['street_address_line_2'];
		}	
		$full_address .=  ' ' . $location_data['city'] . ', ' . $location_data['state'] . ' ' . $location_data['zipcode'];		
		
		$enc_address = urlencode($full_address);
		$location_data['google_maps_url'] = 'https://maps.google.com/?q=' . $enc_address;
		$location_data['google_maps_iframe_url'] = 'https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $enc_address . '&amp;t=h&amp;ie=UTF8&amp;hq=&amp;hnear=' . $enc_address . '&amp;z=14&amp;output=embed';
		$location_data['google_maps_directions_url'] = 'https://maps.google.com/maps?saddr=current+location&daddr=' . urlencode($full_address);
		
		return $location_data;
	}

	function flush_location_rewrite_rules()
	{
		//create post type with new setting so we can flush rules
		//load setting
		$single_view_slug_option = get_option('loc_p_single_view_slug', 'locations');

		//defaults to locations
		$single_view_slug = !empty($single_view_slug_option) ? $single_view_slug_option : 'locations';
		
		$postType = array(
			'name' => 'Location', 
			'plural' => 'Locations', 
			'slug' => $single_view_slug, 
			'post_type_name' => 'location',
			'menu_icon' => 'dashicons-location-alt',
			
		);
		
		$customFields = array(); //not needed for our current purposes
		
		$rewrite_post_type = new GoldPlugins_CustomPostType($postType, $customFields);
		$rewrite_post_type->registerPostTypes();
		
		//update slugs as needed
		flush_rewrite_rules();
	}

	/* Returns true/false indicating whether or not this is the Pro version */
	function isValidKey($skipCache = false)
	{
		// "cache" the key check status with a member variable
		if (!$skipCache && isset($this->valid_key)) {
			return $this->valid_key;
		}
		
		// first time running, so check the key and cache the result
		$email = get_option('loc_p_registration_email', '');
		$webaddress = get_option('loc_p_registration_website_url', '');
		$key = get_option('loc_p_registration_api_key', '');
		
		$checker = new LOC_P_KG();
		$computedKey = $checker->computeKey($webaddress, $email);
		$computedKeyEJ = $checker->computeKeyEJ($email);

		if ($key == $computedKey || $key == $computedKeyEJ) {
			$this->valid_key = true;
			return true;
		} else {
			$plugin = "locations-pro/locations-pro.php";			
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			if(is_plugin_active($plugin)) {
				$this->valid_key = true;
				return true;
			}
		}
		$this->valid_key = false;
		return false;
	}

	/* Returns true/false indicating whether or not this is the Pro version */
	function isValidMSKey($skipCache = false)
	{
		$plugin = "locations-pro/locations-pro.php";			
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		if(is_plugin_active($plugin)) {
			$this->valid_key = true;
			return true;
		}
			
		return false;
	}
	
	function add_vcard_post_class( $classes ) 
	{
		global $post;
		if ($post->post_type == 'location') {
			$classes[] = 'vcard';
		}
		return $classes;
	}
	
	function add_vcard_title_class( $title, $id = null )
	{
		global $post;
		if ( isset($post->post_type) && $post->post_type == 'location' && is_single() ) {
			return '<span class="fn org">' . $title . '</span>';
		} else {
			return $title;
		}
	}

	function add_extra_classes_to_admin_menu() 
	{
		global $menu;
		
		if( !empty($menu) ){ // i've seen it happen before
			foreach( $menu as $key => $value ) {
				$extra_classes = 'locations_admin_menu';
				$extra_classes .= $this->isValidKey()
								  ? ' locations_pro_admin_menu'
								  : ' locations_free_admin_menu';
				
				if( 'Locations' == $value[0] ) {
					$menu[$key][4] .= ' ' . $extra_classes;
				}
			}
		}
	}
	
	function add_gutenburg_block_category ( $categories, $post ) 
	{
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'locations-plugin',
					'title' => 'Locations',
				),
			)
		);
	}

	function provide_config_data_to_admin()
	{
		$translation_array = array(
			'is_pro' => $this->isValidKey(),
		);
		wp_localize_script( 'single-location-block-editor', 'locations_admin_single_location', $translation_array );
	}	
	
	
}
$gp_lp = new LocationsPlugin();

// Initialize any addons now
do_action('locations_bootstrap');
