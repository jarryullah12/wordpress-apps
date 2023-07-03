<?php
/*
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

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class singleLocationWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array(
			'classname' => 'singleLocationWidget',
			'description' => __('Displays the details for a single Location.', 'locations')
		);
		parent::__construct('singleLocationWidget', 'Locations - Single Location', $widget_ops);
	}

	// PHP4 style constructor for backwards compatibility
	function singleLocationWidget() {
		$this->__construct();
	}
	
	function form($instance){
		$instance = wp_parse_args(
			(array) $instance, 
			array( 
				'title' => '', 
				'locationid' => null, 
				'show_location_image' => false, 
				'caption' => '', 
				'style' => 'small',
				'show_phone' => true,
				'show_fax' => true,
				'show_email' => true,
				'show_info' => true,
				'show_map' => true
			) 
		);
		
		$title = $instance['title'];
		$locationid = $instance['locationid'];
		$show_location_image = $instance['show_location_image'];
		$caption = $instance['caption'];		
		$style = $instance['style'];
		$show_phone = isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$show_fax = isset($instance['show_fax']) ? $instance['show_fax'] : true;
		$show_email = isset($instance['show_email']) ? $instance['show_email'] : true;
		$show_info = isset($instance['show_info']) ? $instance['show_info'] : true;
		$show_map = isset($instance['show_map']) ? $instance['show_map'] : true;
		?>
		<div class="gp_widget_form_wrapper">
			<p class="hide_in_popup">
				<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>">Widget Title:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>

			<p>
				<?php
					$locations = get_posts('post_type=location&posts_per_page=-1&nopaging=true&orderby=title&order=ASC');
				?>
				<label for="<?php echo esc_attr( $this->get_field_id('locationid') ); ?>">Location to Display</label>
				<select id="<?php echo esc_attr( $this->get_field_id('locationid') ); ?>" name="<?php echo esc_attr( $this->get_field_name('locationid') ); ?>"data-shortcode-key="id">
				<?php if($locations) : foreach ( $locations as $location  ) : ?>
					<option value="<?php echo esc_attr( $location->ID ); ?>"  <?php if($locationid == $location->ID): ?> selected="SELECTED" <?php endif; ?> ><?php echo wp_kses( $location->post_title, 'post' ); ?></option>
				<?php endforeach; endif;?>
				</select>
			</p>
			<fieldset class="radio_text_input">
				<legend>Fields To Display</legend>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id('show_phone') ); ?>">
						<input name="<?php echo esc_attr( $this->get_field_name('show_phone') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_phone') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_phone') ); ?>" type="checkbox" value="1" <?php if($show_phone){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						Phone
					</label>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id('show_fax') ); ?>">
						<input name="<?php echo esc_attr( $this->get_field_name('show_fax') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_fax') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_fax') ); ?>" type="checkbox" value="1" <?php if($show_fax){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						Fax Number
					</label>
				</p>
				<p>					
					<label for="<?php echo esc_attr( $this->get_field_id('show_email') ); ?>">
						<input name="<?php echo esc_attr( $this->get_field_name('show_email') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_email') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_email') ); ?>" type="checkbox" value="1" <?php if($show_email){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						E-Mail Address
					</label>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id('show_info') ); ?>">
						<input name="<?php echo esc_attr( $this->get_field_name('show_info') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_info') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_info') ); ?>" type="checkbox" value="1" <?php if($show_info){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						Info
					</label>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id('show_map') ); ?>">
						<input name="<?php echo esc_attr( $this->get_field_name('show_map') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_map') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_map') ); ?>" type="checkbox" value="1" <?php if($show_map){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						Map
					</label>
				</p>					
				<p>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_location_image') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_location_image') ); ?>" type="checkbox" value="1" <?php if($show_location_image){ ?>checked="CHECKED"<?php } ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id('show_location_image') ); ?>">Show Location Image</label>
				</p>
			</fieldset>
		</div>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['locationid'] = $new_instance['locationid'];
		$instance['show_location_image'] = $new_instance['show_location_image'];
		$instance['style'] = $new_instance['style'];
		$instance['caption'] = $new_instance['caption'];
		$instance['show_phone'] = $new_instance['show_phone'];
		$instance['show_fax'] = $new_instance['show_fax'];
		$instance['show_email'] = $new_instance['show_email'];
		$instance['show_info'] = $new_instance['show_info'];
		$instance['show_map'] = $new_instance['show_map'];
		
		return $instance;
	}

	function widget($args, $instance){
		$gp_lp = new LocationsPlugin();
		
		extract($args, EXTR_SKIP);

		echo wp_kses($before_widget, 'post');
		
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$locationid = empty($instance['locationid']) ? false : $instance['locationid'];
		$show_location_image = empty($instance['show_location_image']) ? false : $instance['show_location_image'];
		$caption = empty($instance['caption']) ? '' : $instance['caption'];
		$style = empty($instance['style']) ? 'small' : $instance['style'];
		$show_phone = empty($instance['show_phone']) ? true : $instance['show_phone'];
		$show_fax = empty($instance['show_fax']) ? true : $instance['show_fax'];
		$show_email = empty($instance['show_email']) ? true : $instance['show_email'];
		$show_info = empty($instance['show_info']) ? true : $instance['show_info'];
		$show_map = empty($instance['show_map']) ? 'per_location' : $instance['show_map'];


		if (!empty($title)){
			echo wp_kses($before_title, 'post');
			echo wp_kses($title, 'strip');
			echo wp_kses($after_title, 'post');
		}
		
		//this function will output a list of locations if the ID isn't passed
		//so we check to be sure an ID has been set (ie, that this isn't the first time the widget was selected)
		//before we output anything, otherwise we end up with a dump of all locations in the widget area
		if ($locationid != false){
			$sc_output = $gp_lp->locations_shortcode(
				array(
					'id' => $locationid, 
					'show_photos' => $show_location_image, 
					'caption' => $caption, 
					'style' => $style,
					'show_phone' => $show_phone,
					'show_fax' => $show_fax,
					'show_email' => $show_email,
					'show_info' => $show_info,
					'show_map' => $show_map
				)
			);
			echo wp_kses( $sc_output, 'post' );			
		}

		echo wp_kses( $after_widget, 'post' );
	} 
}
?>