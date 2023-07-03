<?php
	class Locations_Plugin_Settings
	{
		var $base_file = '';
		var $is_pro = false;
		var $tabs;
		
		function __construct($base_file, $is_pro = false)
		{
			$this->base_file = $base_file;
			$this->is_pro = $is_pro;
			// load Sajak
			$this->tabs = new GP_Sajak( array(
				'header_label' => 'Locations Plugin Settings',
				'settings_field_key' => 'locations_settings'
			) );
			$this->setup_tabs();		
			$this->add_hooks();
		}
		
		function add_hooks()
		{
			$plugin = plugin_basename($this->base_file);
			add_filter( "plugin_action_links_{$plugin}", array($this, 'add_settings_link_to_plugin_action_links') );

			add_action( 'admin_init', array($this, 'register_settings') );
			add_action( 'admin_menu', array($this, 'add_locations_settings_page') );
		
			/* Add a menu item for the Upgrade page. Add late, to end of list */
			add_action( 'admin_menu', array($this, 'add_upgrade_to_pro_link'), 20 );			
		}
	
		function register_settings()
		{
			register_setting( 'locations_settings', 'locations_settings', array($this, 'update_options_new') );
		}
		
		function update_options_new()
		{
			// save normal settings if present
			$plugin_options = $this->get_plugin_options();
			foreach($plugin_options as $opt) {
				$name = $opt['name'];
				if (isset($_POST[$name])) {
					$val = sanitize_text_field($_POST[$name]);
					update_option($name, $val);
				}
			}

			// save registration keys if present
			$reg_keys = array(
				'loc_p_registration_api_key',
				'loc_p_registration_website_url',
				'loc_p_registration_email'
			);
			foreach($reg_keys as $name) {
				if (isset($_POST[$name])) {
					$val = sanitize_text_field($_POST[$name]);
					update_option($name, $val);
				}
			}
			
			// refresh plugin + WordPress config
			do_action('locations_flush_rewrite_rules');
			
			// show success message to user
			$this->tabs->flash("Settings saved.");
		}
		
		function get_plugin_options($load_values = true)
		{
			$general_options = $this->get_general_options($load_values);
			$store_locator_options = $this->get_store_locator_options($load_values);
			return array_merge($general_options, $store_locator_options);
		}
		
		function get_general_options($load_values = true)
		{
			$plugin_options = array();
			$plugin_options[] = array('name' => 'loc_p_google_maps_api_key', 'label' => 'Google Maps API Key', 'desc' => 'Without a free Google Maps API key, your plugin will not work.<br><br><strong>Important:</strong> Be sure to enable your key for both the <strong style="color:forestgreen"><a href="https://developers.google.com/maps/documentation/javascript/">Google Maps Javascript API</a></strong> and the <strong style="color:forestgreen"><a href="https://developers.google.com/maps/documentation/geocoding/">Google Maps Geocoder API</a></strong>.<br><br><a class="button" style="font-style:normal" href="https://developers.google.com/maps/web/" target="_blank">Get Your Free Google Maps API Key</a>' );
			$plugin_options[] = array('name' => 'loc_p_show_fax_number', 'label' => 'Show Fax Number', 'type' => 'checkbox', 'default' => '1', 'desc' => 'If checked, the Fax Number field will be displayed on the Add and Edit Location screens, as well as on the front end location display.');
			$plugin_options[] = array('name' => 'loc_p_show_email', 'label' => 'Show Email', 'type' => 'checkbox', 'default' => '1', 'desc' => 'If checked, the Email field will be displayed on the Add and Edit Location screens, as well as on the front end location display.');
			$plugin_options[] = array('name' => 'loc_p_show_info', 'label' => 'Show Info Box', 'type' => 'checkbox', 'default' => '1', 'desc' => 'If checked, the Additional Info field will be displayed on the Add and Edit Location screens, as well as on the front end location display.');
			$plugin_options[] = array('name' => 'loc_p_show_map', 
							   'label' => 'Show Google Maps',
							   'desc' => '',
							   'type' => 'radio',
							   'options' => array('per_location' => 'Use Location\'s Own Setting', 'always' => 'Always show Google Maps', 'never' => 'Never show Google Maps'),
							   'default' => 'per_location' );
			$plugin_options[] = array(
				'name' => 'loc_p_single_view_slug',
				'label' => 'Single View Slug',
				'default' => 'locations',
				'desc' => 'The slug to be used in the permalinks for single locations.  E.g, "http://www.yoursite.com/<strong>locations</strong>/roth-street-store".'
			);
			
									   
			// load the current setting values from the database (normal options)
			if ($load_values) {
				foreach($plugin_options as $index => $opt) {
					$name = $opt['name'];
					$def = isset($opt['default']) ? $opt['default'] : '';
					$plugin_options[$index]['val'] = get_option($opt['name'], $def);
				}
			}
			
			return $plugin_options;
		}
		
		function get_store_locator_options($load_values = true)
		{
			// add Store Locator settings
			$plugin_options[] = array('name' => 'loc_p_miles_or_km', 'label' => 'Miles or Kilometers', 'desc' => 'Should the store locator show distances in miles or kilometers?', 'type' => 'radio', 'options' => array('miles' => 'Miles', 'km' => 'Kilometers'), 'default' => 'miles' );
			$plugin_options[] = array('name' => 'loc_p_search_radius', 'label' => 'Search Radius', 'desc' => 'When a user searches for nearby locations, show stores that are within this distance.', 'class' => 'small' );
			$plugin_options[] = array('name' => 'loc_p_default_latitude', 'label' => 'Default Latitude:', 'desc' => 'If we are unable to geolocate the visitor by their IP address, center the map to this latitude.' , 'default' => '39.8282', 'class' => 'small'); // default to USA
			$plugin_options[] = array('name' => 'loc_p_default_longitude', 'label' => 'Default Longitude:', 'desc' => 'If we are unable to geolocate the visitor by their IP address, center the map to this longitude.', 'default' => '-98.5795', 'class' => 'small'); // default to USA
			$plugin_options[] = array('name' => 'loc_p_default_country', 'label' => 'Default Country:', 'desc' => 'If a location has no country specified, use this country.', 'default' => 'United States', 'class' => 'small');
									   
			// load the current setting values from the database (normal options)
			if ($load_values) {
				foreach($plugin_options as $index => $opt) {
					$name = $opt['name'];
					$def = isset($opt['default']) ? $opt['default'] : '';
					$plugin_options[$index]['val'] = get_option($opt['name'], $def);
				}
			}
			
			return $plugin_options;
		}

		// setup all of the tabs for Sajak
		private function setup_tabs()
		{
			$this->tabs->add_tab(
				'general_settings', 
				'General Settings',
				array($this, 'output_general_settings_fields'),
				array(
					'icon' => 'cog',
					'show_save_button' => true
				)
			);
			$this->tabs->add_tab(
				'store_locator',
				'Store Locator',
				array($this, 'output_store_locator_settings_fields'),
				array(
					'icon' => 'cogs',
					'show_save_button' => true
				)
			);
			$this->tabs->add_tab(
				'pro_registration',
				'Pro Registration',
				array($this, 'output_pro_registration_fields'),
				array(
					'icon' => 'key',
					'show_save_button' => true
				)
			);
		}
		
		/* Create a menu item for the Location Settings page */
		function add_locations_settings_page()
		{				
			$top_level_slug = 'edit.php?post_type=location';
		
			$submenu_pages = array(
				array(
					'label' => 'Settings',
					'page_title' => 'Settings',
					'role' => 'administrator',
					'slug' => 'locations-settings',
					'callback' => array($this, 'output_location_settings_page')
				),
				/*array(
					'label' => 'Shortcode Generator',
					'page_title' => 'Shortcode Generator',
					'role' => 'administrator',
					'slug' => 'company-directory-shortcode-generator',
					'callback' => array($this, 'shortcode_generator_page')
				),*/
				array(
					'label' => 'Help & Instructions',
					'page_title' => 'Help & Instructions',
					'role' => 'administrator',
					'slug' => 'locations-help',
					'callback' => array($this, 'render_help_page')
				)
			);
			
			// allow addons to add menus now
			$submenu_pages = apply_filters('locations_admin_submenu_pages', $submenu_pages, $top_level_slug);
			
			foreach ($submenu_pages as $submenu_page) {			
				add_submenu_page(
					$top_level_slug,
					$submenu_page['label'],
					$submenu_page['page_title'],
					$submenu_page['role'],
					$submenu_page['slug'],
					$submenu_page['callback']
				);
			}			
		}

		/* Render the Location settings page, and save options that may be changed */
		function output_location_settings_page()
		{
			// Check that the user is allowed to update options
			if (!current_user_can('manage_options')) {
				wp_die('You do not have sufficient permissions to access this page.');
			}
	?>
			<script type="text/javascript">
				jQuery(function () {
					if (typeof(gold_plugins_init_coupon_box) == 'function') {
						gold_plugins_init_coupon_box();
					}
				});
			</script>
			<div class="wrap locations-admin-wrap <?php echo ( $this->is_pro ? 'gp_pro' : 'gp_not_pro' );?>">
			<?php
				// output Sajak tabs
				$this->tabs->display(); 
			?>	
			</div>		
			
	<?php
		}

		function output_general_settings_fields()
		{
			echo '<input type="hidden" name="update_settings" value="1" />';
		
			$plugin_options = $this->get_general_options();
		?>
			<h3>General Settings</h3>
			<table class="form-table">
				<?php foreach($plugin_options as $opt):
						$this->output_option_row($opt);
				endforeach; ?>
			</table>
		<?php	
		}
		
		function output_store_locator_settings_fields()
		{
			$options = $this->get_store_locator_options();
		?>
			<h3>Store Locator Settings</h3>
			<table class="form-table">
				<?php foreach($options as $opt):
						$this->output_option_row($opt);
				endforeach; ?>
			</table>
		<?php	
		}
		
		function output_pro_registration_fields()
		{
		?>
			<h3>Locations Pro Registration</h3>
			<?php if ( $this->is_pro ): ?>
			<p class="message success"><span class="fa fa-check"></span> Your Locations Pro API key is active and valid.</p>
			<?php else: ?>
			<p>Please enter the email you used to purchase and your API Key below to automatically upgrade your plugin.</p>
			<p>Your data and settings will not be affected.</p>
			<?php endif; ?>
		<?php
			$this->pro_registration_form();
		}
		
		/* Helper function: Outputs a table row containing an input which can edit the given option */
		private function output_option_row($opt)
		{
			$def = isset($opt['default']) ? $opt['default'] : '';
			$val = isset($opt['val']) ? $opt['val'] : $def;
			$type = isset($opt['type']) ? $opt['type'] : 'text';
			$desc = isset($opt['desc']) ? $opt['desc'] : '';
			// class only applies to text inputs
			$class = isset($opt['class']) ? $opt['class'] : ''; 
	?>
			<tr valign="top">
				<th scope="row">
					<label for="<?php echo esc_attr($opt['name']) ?>">
						<?php echo esc_html($opt['label']) ?>
					</label>
				</th>
				<td>
				<?php
					$def = isset($opt['default']) ? $opt['default'] : '';
					$val = isset($opt['val']) ? $opt['val'] : $def;
					switch ($type):
						case 'checkbox':
				?>
						<input type="hidden" id="<?php echo esc_attr($opt['name']); ?>" name="<?php echo esc_attr($opt['name']); ?>" value="0" />
						<?php if ($val == '1'): ?>
						<input type="checkbox" id="<?php echo esc_attr($opt['name']); ?>" name="<?php echo esc_attr($opt['name']); ?>" checked="checked" value="1" />
						<?php else: ?>
						<input type="checkbox" id="<?php echo esc_attr($opt['name']); ?>" name="<?php echo esc_attr($opt['name']); ?>" value="1" />
						<?php endif; ?>
				<?php	
						break;

						case 'radio':
				?>
						<fieldset>
						<?php foreach($opt['options'] as $choice_val => $choice_display): ?>
							<label title="<?php echo esc_attr($choice_val); ?>">
								<?php if ( $val == $choice_val ) : ?>
								<input type="radio" name="<?php echo esc_attr($opt['name']); ?>" value="<?php echo esc_attr($choice_val); ?>" checked="checked" />
								<?php else: ?>
								<input type="radio" name="<?php echo esc_attr($opt['name']); ?>" value="<?php echo esc_attr($choice_val); ?>" />
								<?php endif; ?>
								<span><?php echo esc_html($choice_display); ?></span>
							</label>
							<br />
						<?php endforeach; ?>
						</fieldset>
				<?php
						break;

						case 'text':
						default:
				?>
						<input type="text" id="<?php echo esc_attr($opt['name']) ?>" name="<?php echo esc_attr($opt['name']) ?>" size="25" value="<?php echo esc_attr($val); ?>" class="<?php echo esc_attr($class); ?>" />
				<?php
						break;
				
					endswitch;
				?>
				
				<?php if (strlen($desc) > 0): ?>
				<p class="description"><?php echo wp_kses( $opt['desc'], 'post' ) ?></p>
				<?php endif; ?>
				
				</td>
			</tr>
	<?php	
		}
		
		/* Renders the Locations Pro registration form */
		function pro_registration_form()
		{
	?>
			<div id="api_keys">
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="loc_p_registration_email">Email:</label>
						</th>
						<td>
							<input type="text" id="loc_p_registration_email" name="loc_p_registration_email" size="25" value="<?php echo get_option('loc_p_registration_email'); ?>" />
							<!--<p class="description"></p>-->
						</td>
					</tr>
					<tr valign="top" style="display: none;">
						<th scope="row">
							<label for="loc_p_registration_website_url">Website URL:</label>
						</th>
						<td>
							<input type="text" id="loc_p_registration_website_url" name="loc_p_registration_website_url" size="25" value="" />
							<!--<p class="description"></p>-->
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="loc_p_registration_api_key">API Key:</label>
						</th>
						<td>
							<input type="text" id="loc_p_registration_api_key" name="loc_p_registration_api_key" size="25" value="<?php echo get_option('loc_p_registration_api_key'); ?>" />
							<!--<p class="description"></p>-->
						</td>
					</tr>
				</table>
			</div>
	<?php

		}
		
		/* Add Upgrade to Pro menu */	
		function add_upgrade_to_pro_link()
		{
			if ( !$this->is_pro ) {
				add_submenu_page(
					'edit.php?post_type=location',
					__('Upgrade To Pro'),
					__('Upgrade To Pro'),
					'administrator',
					'locations-upgrade-to-pro',
					array($this, 'render_upgrade_page')
				);
			}
		}
		
		/* 
		 * Output the upgrade page
		 */
		function render_upgrade_page()
		{
			//setup coupon box
			$upgrade_page_settings = array(
				'plugin_name' 		=> 'Locations Pro',
				'pitch' 			=> "When you upgrade, you'll instantly unlock advanced features including Import & Export, and more!",
				'learn_more_url' 	=> 'https://goldplugins.com/our-plugins/locations-pro/?utm_source=cpn_box&utm_campaign=upgrade&utm_banner=learn_more',
				'upgrade_url' 		=> 'https://goldplugins.com/our-plugins/locations-pro/upgrade-to-locations-pro/?utm_source=plugin_menu&utm_campaign=upgrade',
				'upgrade_url_promo' => 'https://goldplugins.com/purchase/locations-pro/single?promo=newsub10',
				'text_domain' => 'locations',
				'testimonial' => array(
					'title' => 'Works like a dream.',
					'body' => 'It works like a dream. Simple and easy to setup. It presents the information in a neat & tidy, and visually appealing, manner.',
					'name' => 'maxhumayun <br>via WordPress.org',
				)
			);
			
			$img_base_url = plugins_url('../assets/img/upgrade/', __FILE__);
			?>		
			<div class="locations_admin_wrap">
				<div class="gp_upgrade">
					<h1 class="gp_upgrade_header">Upgrade To Locations Pro</h1>
					<div class="gp_upgrade_body">
					
						<div class="header_wrapper">
							<div class="gp_slideshow">
								<ul>
									<li class="slide"><img src="<?php echo esc_url( $img_base_url ) . 'import-export.png'; ?>" alt="Screenshot of Import Wizard - &amp; Export Wizard" /><div class="caption">The Import &amp; Export Wizard supports over 200 file types!</div></li>
									<li class="slide"><img src="<?php echo esc_url( $img_base_url ) . 'locations-directory-widget.png'; ?>" alt="Screenshot of Store Directory Widget with Google Map and Clustering feature" /><div class="caption">The Store Directory Widget supports many locations with ease</div></li>
									<li class="slide"><img src="<?php echo esc_url( $img_base_url ) . 'list-of-locations.png'; ?>" alt="Screenshot of List of Locations in Search Results" /><div class="caption">Visitors can easily your locations by City and State</div></li>
								</ul>
								<a href="#" class="control_next">></a>
								<a href="#" class="control_prev"><</a>							
							</div>

							<script type="text/javascript">
								jQuery(function () {
									if (typeof(gold_plugins_init_upgrade_slideshow) == 'function') {
										gold_plugins_init_upgrade_slideshow();
									}
								});
							</script>						
							<div class="customer_testimonial">
									<div class="stars">
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
									</div>
									<p class="customer_testimonial_title"><strong><?php echo wp_kses( $upgrade_page_settings['testimonial']['title'], 'post' ); ?></strong></p>
									"<?php echo wp_kses( $upgrade_page_settings['testimonial']['body'], 'post' ); ?>"
									<p class="author">&mdash; <?php echo wp_kses( $upgrade_page_settings['testimonial']['name'], 'post' ); ?></p>
							</div>
						</div>
						<div style="clear:both;"></div>
							<p class="upgrade_intro">Locations Pro is the professional edition of Locations, built from the ground-up to accomodate large organizations. Its a great choice for any organization with more than 20 locations, but easily handles thousands.</p>					<div class="upgrade_left_col">
							<div class="upgrade_left_col_inner">
								<h3>Locations Pro Adds Powerful New Features, Including:</h3>
								<ul>
									<li>Import from any Excel-compatible file (over 200 formats supported)</li>
									<li>Export your Locations to CSV files that work with any system</li>
									<li>Display a Store Directory with an integrated Google Map</li>
									<li>Allow your customers to browse your stores by region and city</li>
									<li>Outstanding support from our developers</li>
									<li>A full year of technical support & automatic updates</li>
									<!--<li>Automatically direct your users to the closest location with the Nearest Location Widget</li>-->
								</ul>

								<p class="all_features_link">And many more! <a href="https://goldplugins.com/downloads/locations-pro/?utm_source=locations_upgrade_page_plugin&amp;utm_campaign=see_all_features">Click here for a full list of features included in Locations Pro</a>.</p>
								<p class="upgrade_button"><a href="https://goldplugins.com/special-offers/upgrade-to-locations-pro/?utm_source=locations_free_plugin&utm_campaign=upgrade_page_button">Learn More</a></p>
							</div>
						</div>
						<div class="bottom_cols">
							<div class="how_to_upgrade">
								<h4>How To Upgrade:</h4>
								<ol>
									<li><a href="https://goldplugins.com/special-offers/upgrade-to-locations-pro/?utm_source=locations_free_plugin&utm_campaign=how_to_upgrade_steps">Purchase an API Key from GoldPlugins.com</a></li>
									<li>Install and Activate the Locations Pro plugin.</li>
									<li>Go to Locations &raquo; License Options menu, enter your API key, and click Activate.</li>
								</ol>
								<p class="upgrade_more">That's all! Upgrading takes just a few moments, and won't affect your data.</p>
							</div>
							<div class="questions">	<h4>Have Questions?</h4>
								<p class="questions_text">We can help. <a href="https://goldplugins.com/contact/">Click here to Contact Us</a>.</p>
								<p class="all_plans_include_support">All plans include a full year of technical support.</p>
							</div>
						</div>
					</div>
					
					<div id="signup_wrapper" class="upgrade_sidebar">
						<div id="mc_embed_signup">
							<div class="save_now">
								<h3>Save 10% Now!</h3>
								<p class="pitch">Subscribe to our newsletter now, and we'll send you a coupon for 10% off your upgrade to the Pro version.</p>
							</div>
							<form action="https://goldplugins.com/atm/atm.php?u=403e206455845b3b4bd0c08dc&amp;id=a70177def0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
								<div class="fields_wrapper">
									<label for="mce-NAME">Your Name (optional)</label>
									<input value="golden" name="NAME" class="name" id="mce-NAME" placeholder="Your Name" type="text">
									<label for="mce-EMAIL">Your Email</label>
									<input value="services@illuminatikarate.com" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required="" type="email">
									<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
									<div style="position: absolute; left: -5000px;"><input name="b_403e206455845b3b4bd0c08dc_6ad78db648" tabindex="-1" value="" type="text"></div>
								</div>
								<div class="clear"><input value="Send My Coupon" name="subscribe" id="mc-embedded-subscribe" class="whiteButton" type="submit"></div>
								<p class="secure"><img src="<?php echo plugins_url( "../assets/img/lock.png", __FILE__); ?>" alt="Lock" width="16px" height="16px">We respect your privacy.</p>
								
								<input id="mc-upgrade-plugin-name" name="mc-upgrade-plugin-name" value="<?php echo htmlentities($upgrade_page_settings['plugin_name']); ?>" type="hidden">
								<input id="mc-upgrade-link-per" name="mc-upgrade-link-per" value="<?php echo esc_attr( $upgrade_page_settings['upgrade_url_promo'] ); ?>" type="hidden">
								<input id="mc-upgrade-link-biz" name="mc-upgrade-link-biz" value="<?php echo esc_attr( $upgrade_page_settings['upgrade_url_promo'] ); ?>" type="hidden">
								<input id="mc-upgrade-link-dev" name="mc-upgrade-link-dev" value="<?php echo esc_attr( $upgrade_page_settings['upgrade_url_promo'] ); ?>" type="hidden">
								<input id="gold_plugins_already_subscribed" name="gold_plugins_already_subscribed" value="0" type="hidden">
							</form>					
						</div>
						
					</div>
				</div>
			</div>
			<script type="text/javascript">
			jQuery(function () {
				if (typeof(locations_gold_plugins_init_coupon_box) == 'function') {
					locations_gold_plugins_init_coupon_box();
				}
			});
			</script>
			<?php
		} 	
			
		//output the help page
		function render_help_page()
		{		
			//instantiate tabs object for output basic settings page tabs
			$tabs = new GP_Sajak( array(
				'header_label' => 'Help &amp; Instructions',
				'settings_field_key' => 'locations-help-settings-group', // can be an array	
				'show_save_button' => false, // hide save buttons for all panels   		
			) );
			
			//$this->settings_page_top(false);
			$tab_list = array();
			$tab_list[] = array(
				'id' => 'help', 
				'label' => __('Help Center', 'locations'),
				'callback' => array($this, 'output_location_help_page'),
				'options' => array('icon' => 'life-buoy')
			);
		
			$tab_list = apply_filters('locations_admin_help_tabs', $tab_list);	
			
			foreach( $tab_list as $tab) {
				$tabs->add_tab(
					$tab['id'],
					$tab['label'],
					$tab['callback'],
					$tab['options']
				);
			}	
			
			$tabs->display();
		}		
		
		/* Render the Location Help & Instructions Page */
		function output_location_help_page()
		{
			?>
			<h3>Help Center</h3>
			<div class="help_box">
				<h4>Have a Question?  Check out our FAQs!</h4>
				<p>Our FAQs contain answers to our most frequently asked questions.  This is a great place to start!</p>
				<p><a class="gp_support_button" target="_blank" href="https://goldplugins.com/documentation/locations-documentation/locations-faqs/?utm_source=help_page">Click Here To Read FAQs</a></p>
			</div>
			<div class="help_box">
				<h4>Looking for Instructions? Check out our Documentation!</h4>
				<p>For a good start to finish explanation of how to add Locations and then display them on your site, check out our Documentation!</p>
				<p><a class="gp_support_button" target="_blank" href="https://goldplugins.com/documentation/locations-documentation/?utm_source=help_page">Click Here To Read Our Docs</a></p>
			</div>
			<?php	
		}
		
		//add an inline link to the settings page, before the "deactivate" link
		function add_settings_link_to_plugin_action_links($links) { 
		  $settings_link = '<a href="edit.php?post_type=location&page=locations-settings">Settings</a>';
		  array_unshift($links, $settings_link); 
		  return $links; 
		}
		
		
	}		
