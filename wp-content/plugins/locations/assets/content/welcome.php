<?php
// Locations Welcome Page template

ob_start();
$learn_more_url = 'https://goldplugins.com/special-offers/upgrade-to-before-after-pro/?utm_source=locations_free&utm_campaign=welcome_screen_upgrade&utm_content=col_1_learn_more';
$settings_url = menu_page_url('locations-license-information', false);
$pro_registration_url = $settings_url;
$utm_str = '?utm_source=locations_free&utm_campaign=welcome_screen_help_links';
$new_post_link = admin_url('post-new.php?post_type=location&guided_tour=1');
$google_maps_link = admin_url('post-new.php?post_type=location&guided_tour=1');
?>


<p class="aloha_intro"><strong>Thank you for installing Locations!</strong> This page is here to help you get up and running. If you're already familiar with Locations, you can skip it and <a href="<?php echo esc_url( $settings_url ); ?>">continue to the Basic Settings page</a>. </p>
<p class="aloha_tip"><strong>Tip:</strong> You can always access this page via the <strong>Locations &raquo; About Plugin</strong> menu.</p>

<h2 style="font-size: 22px;font-weight: normal;margin-top: 40px;color: #343434;border-bottom: 1px solid #eee;padding-bottom: 15px;">Getting Started With Locations</h2>
<ul class="getting_started_steps">
	<li>
		<h3><span style="color:green">Step 1:</span> Get A Free Google Maps API Key</h3>
		<p>The first thing you'll want to do is to create a free Google Maps & Geocoder API key. This will allow Locations to automatically convert your addresses into their latitudes and longitudes, and is required for the store Locator to function.
		<div style="background-color: #f4fbff; border: 1px solid lightskyblue; padding: 10px 20px;">
			<p><a href="<?php echo esc_url( $google_maps_link ); ?>" class="button">Get A Free Google Maps API Key</a> &nbsp; <a href="<?php echo esc_url( $google_maps_link ); ?>" class="button">Get A Free Google Geocoding API Key</a></p>
			<p><strong>Instructions:</strong><br>Click each button seperately to visit its page on Google. When the page loads, click the "Get A Key" button and follow the on-screen instructions. You will need to click "Get A Key" for both the Maps and the Geocoding APIs, but will receive only a single key.</p>
		</div>
		<p>Once you have obtained your free API key from Google, enter it on the <a href="<?php echo esc_url( admin_url('edit.php?post_type=location&page=locations-settings#tab-general_settings') ); ?>">Locations Settings page, under the General Settings tab</a>. You can find this page by visiting the Locations &raquo; Settings menu in WordPress.</p>
	</li>
	
	<li>
		<h3><span style="color:green">Step 2:</span> Enter Your Locations</h3>
		<p>Adding Locations is just like creating a new Post in WordPress. Simply navigate to the Locations &raquo; Add New Locations menu, or click the button below.</p>
		<p><a href="<?php echo esc_url( $new_post_link ); ?>" class="button">Add a New Location</a></p>
		<p>Give each location a Title, and then fill out the rest of the fields as much as you'd like. You don't need to enter latitude or longitude - these will be converted (geocoded) automatically using the Google Maps API.</p>
	</li>

	<li>
		<h3><span style="color:green">Step 3:</span> Setup Your Store Locator</h3>
		<p>To create a Store Locator, simply edit or create the page on your site where you'd like your Store Locator to appear. Then click the Locations menu above the post editor, and select Store Locator.</p>
		<p>Choose the options you'd like for your Store Locator and then click the Insert Now button. This will add a shortcode to your page, which will render your Store Locator in its place.</p>
		<p>If you'd like to change the way your Store Locator looks or behaves in the future, just delete this shortcode and replace it with a new one, using this same process.</p>
	</li>
</ul>
<br>
<p><strong>Congratulations!</strong> If you've followed the three steps above, you should now have a working Store Locator on your website. You can always add, update, or remove locations, and the changes will be instantly reflected in your Store Locator.</p>
<p>To find out what else you can do with Locations, be sure to check out the links below.</p>
<br>
<div class="three_col" style="border-top:1px solid #eee; padding-top: 20px">
	<div class="col">
		<?php if ($is_pro): ?>
			<h3>Locations Pro: Active</h3>
			<p class="plugin_activated">Locations Pro is licensed and active.</p>
			<a href="<?php echo esc_url($pro_registration_url); ?>">Registration Settings</a>
		<?php else: ?>
			<h3>Upgrade To Pro</h3>
			<p>Locations Pro is the Professional, fully-functional version of Locations, which features technical support and access to all Pro&nbsp;features.</p>
			<a class="button" href="<?php echo esc_url($learn_more_url); ?>">Click Here To Learn More</a>
		<?php endif; ?>
	</div>
	<div class="col">
		<h3>Getting Started</h3>
		<ul>
			<li><a href="<?php echo esc_url( $new_post_link ); ?>">Click Here To Add Your First Goal</a></li>
			<li><a href="https://goldplugins.com/documentation/before-after-documentation/before-after-pro-configuration-and-usage-instructions/<?php echo esc_url($utm_str); ?>">Getting Started With Locations</a></li>
			<li><a href="https://goldplugins.com/documentation/before-after-documentation/how-to-create-a-lead-capture-form-with-before-after-pro/<?php echo esc_url($utm_str); ?>">How To Create A Lead Capture Form</a></li>
			<li><a href="https://goldplugins.com/documentation/before-after-documentation/before-after-faqs/<?php echo esc_url($utm_str); ?>">Frequently Asked Questions (FAQs)</a></li>
			<li><a href="https://goldplugins.com/contact/<?php echo esc_url($utm_str); ?>">Contact Technical Support</a></li>
		</ul>
	</div>
	<div class="col">
		<h3>Further Reading</h3>
		<ul>
			<li><a href="https://goldplugins.com/documentation/before-after-documentation/<?php echo esc_url($utm_str); ?>">Locations Documentation</a></li>
			<li><a href="https://wordpress.org/support/plugin/before-and-after/<?php echo esc_url($utm_str); ?>">WordPress Support Forum</a></li>
			<li><a href="https://goldplugins.com/documentation/before-after-documentation/before-after-changelog/<?php echo esc_url($utm_str); ?>">Recent Changes</a></li>
			<li><a href="https://goldplugins.com/<?php echo esc_url($utm_str); ?>">Gold Plugins Website</a></li>
		</ul>
	</div>
</div>

<div class="continue_to_settings">
	<p><a href="<?php echo esc_url( $settings_url ); ?>">Continue to Basic Settings &raquo;</a></p>
</div>

<?php 
$content =  ob_get_contents();
ob_end_clean();
return $content;