<?php
	class Locations_Plugin_Notices
	{
		var $Geocoder;
		var $api_key_status = false;
		
		function __construct( $Geocoder )
		{
			$this->Geocoder = $Geocoder;
			$this->add_hooks();
		}
		
		function add_hooks()
		{
			add_action( 'plugins_loaded', array($this, 'check_for_api_key') );
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_inline_script_for_notices') );
			add_action( 'wp_ajax_locations_dismiss_google_maps_api_key_notice', array($this, 'dismiss_google_maps_api_key_notice') );
		}
	
		function check_for_api_key()
		{
			if ( is_admin() && current_user_can('manage_options') ) { // && empty( get_option('loc_p_google_maps_api_key', '') ) ) {
				
				$hide_google_maps_api_key_notice = get_option('locations_hide_google_maps_api_key_notice', '');
				
				// if its not a Locations page, and the notice has been dismissed, hide it now
				if ( ( false === strpos($_SERVER['REQUEST_URI'], 'location') )
					 && !empty( $hide_google_maps_api_key_notice ) ) {
					return;
				}

				$this->api_key_status = $this->Geocoder->test_api_key();
				if ( $this->api_key_status !== true ) {			
					add_action( 'admin_notices', array($this, 'show_api_key_notice') );
				}
			}
		}
		
		function show_api_key_notice()
		{
			// render the message
			$settings_url = admin_url('edit.php?post_type=location&page=locations-settings');		
			$api_key = get_option('loc_p_google_maps_api_key', '');
			
			if ( empty($api_key) ) {			
				$message = sprintf( '<p><a href="%s">%s</a> %s</p>',
									$settings_url,
									__('Please enter your Google Maps API Key'),
									__('in order to use Google Maps with the Locations plugin.') );
			}
			else if ( !empty($this->api_key_status) ) {
				$message = sprintf( '<h2>Locations</h2><p><strong>%s</strong> <em>%s</em></p>',
									__('There is a problem with your Google Maps API Key: '),
									__($this->api_key_status) );									
									
				if ( strpos($this->api_key_status, 'Browser API keys cannot have referer restrictions') !== false ) {
					$google_fix_url = 'https://developers.google.com/maps/faq#switch-key-type';
					$message .= sprintf( __('<p><em><a href="%s" target="_blank">%s</a></em></p>'),
										 $google_fix_url,
										 __('How To Fix This Issue') );
				}
										 
				// link to location settings
				$message .= sprintf( '<p style="margin:15px 0;"><a class="button" href="%s">%s</a></p>',
									 $settings_url,
									 __('Go To') . ' Locations ' . __('Settings') );
			}
			else {
				$message = sprintf( '<p>%s <a href="%s">%s</a></p>',
									__('<strong>[Locations]</strong> There is an unknown problem with your Google Maps API Key.'),
									$settings_url,
									__('Update your API Key here.') );				
			}
								 
			$div_id = 'locations_google_maps_api_key_notice';
			$is_dismissable  = ( false === strpos($_SERVER['REQUEST_URI'], 'location') );
			
			printf ( '<div id="%s" class="notice notice-%s %s">%s</div>',
					 $div_id,
					'success',
					 $is_dismissable ? ' is-dismissible' : '',
					 $message );		
		}
			

		/**
		 * Adds an inline script to watch for clicks on the "Pro plugin required" 
		 * notice's dismiss button
		 */
		function enqueue_inline_script_for_notices($hook = '')
		{
			$js = '		
			jQuery(function () {
				jQuery("#locations_google_maps_api_key_notice").on("click", ".notice-dismiss", function () {
					jQuery.post(
						ajaxurl, 
						{
							action: "locations_dismiss_google_maps_api_key_notice"
						}
					);
				});
			});		
			';
			if ( !wp_script_is( 'jquery', 'done' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			// note: attach to jquery-core, not jquery, or it won't fire
			wp_add_inline_script('jquery-core', $js);		
		}
		
		/**
		 * AJAX hook - records dismissal of the "Pro plugin required" notice.
		 */
		function dismiss_google_maps_api_key_notice()
		{
			update_option('locations_hide_google_maps_api_key_notice', 1);
			wp_die('OK');
		}
		
	}
