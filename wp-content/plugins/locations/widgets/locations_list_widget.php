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

class locationsListWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array(
			'classname' => 'locationsListWidget',
			'description' => __('Displays a list of all your Locations. You can choose which Locations are shown.', 'locations')
		);
		parent::__construct('locationsListWidget', 'Locations - All Locations', $widget_ops);
	}

	// PHP4 style constructor for backwards compatibility
	function locationsListWidget() {
		$this->__construct();
	}

	function form($instance){
		$instance = wp_parse_args( 
			(array) $instance, 
			array(
				'title' => '',
				'show_location_image' => false,
				'style' => 'small',
				'category' => '',
				'show_phone' => true,
				'show_fax' => true,
				'show_email' => true,
				'show_info' => true,
				'show_map' => 'per_location',
				'order' => 'ASC',
				'order_by' => 'title',
			)
		);
		
		$title = $instance['title'];
		$show_location_image = $instance['show_location_image'];	
		$style = $instance['style'];
		$category = $instance['category'];
		$show_phone = isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$show_fax = isset($instance['show_fax']) ? $instance['show_fax'] : true;
		$show_email = isset($instance['show_email']) ? $instance['show_email'] : true;
		$show_info = isset($instance['show_info']) ? $instance['show_info'] : true;
		$show_map = isset($instance['show_map']) ? $instance['show_map'] : 'per_location';
		$order = $instance['order'];
		$order_by = $instance['order_by'];
		?>
		<div class="gp_widget_form_wrapper">
			<p class="hide_in_popup">
				<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>">Widget Title: </label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('category') ); ?>">Filter By Category:</label><br/>
				<?php $categories = get_terms( 'location-categories', 'orderby=title&hide_empty=0' ); ?>
				<select id="<?php echo esc_attr( $this->get_field_id('category') ); ?>" name="<?php echo esc_attr( $this->get_field_name('category') ); ?>">
					<option value="all">All Categories</option>
					<?php foreach($categories as $cat):?>
					<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php if($category == $cat->term_id):?>selected="SELECTED"<?php endif; ?>><?php echo wp_kses( $cat->name, 'strip' ); ?></option>
					<?php endforeach; ?>
				</select>
				<br/>
				<em><a href="<?php echo admin_url('edit-tags.php?taxonomy=location-categories&post_type=location'); ?>">Manage Categories</a></em>
			</p>
			
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('order') ); ?>">Order:</label><br/>
				<select id="<?php echo esc_attr( $this->get_field_id('order_by') ); ?>" name="<?php echo esc_attr( $this->get_field_name('order_by') ); ?>" class="multi_left" data-shortcode-key="orderby">
					<option value="title" <?php if($order_by == "title"): ?>selected="SELECTED"<?php endif; ?>>Title</option>
					<option value="rand" <?php if($order_by == "rand"): ?>selected="SELECTED"<?php endif; ?>>Random</option>
					<option value="id" <?php if($order_by == "id"): ?>selected="SELECTED"<?php endif; ?>>ID</option>
					<option value="author" <?php if($order_by == "author"): ?>selected="SELECTED"<?php endif; ?>>Author</option>
					<option value="name" <?php if($order_by == "name"): ?>selected="SELECTED"<?php endif; ?>>Name</option>
					<option value="date" <?php if($order_by == "date"): ?>selected="SELECTED"<?php endif; ?>>Date</option>
					<option value="last_modified" <?php if($order_by == "last_modified"): ?>selected="SELECTED"<?php endif; ?>>Last Modified</option>
					<option value="parent_id" <?php if($order_by == "parent_id"): ?>selected="SELECTED"<?php endif; ?>>Parent ID</option>
				</select>
				<select id="<?php echo esc_attr( $this->get_field_id('order') ); ?>" name="<?php echo esc_attr( $this->get_field_name('order') ); ?>" class="multi_right">
					<option value="ASC" <?php if($order == "ASC"): ?>selected="SELECTED"<?php endif; ?>>Ascending (ASC)</option>
					<option value="DESC" <?php if($order == "DESC"): ?>selected="SELECTED"<?php endif; ?>>Descending (DESC)</option>
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
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_location_image') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_location_image') ); ?>" type="checkbox" value="1" <?php if($show_location_image){ ?>checked="CHECKED"<?php } ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id('show_location_image') ); ?>">Show Location Images</label>
				</p>
			</fieldset>
			
			<fieldset class="radio_text_input">
				<legend>Display Maps</legend>
				<div class="radio_wrapper">
					<p class="radio_option">
						<label>
							<input type="radio" name="<?php echo esc_attr( $this->get_field_name('show_map') ); ?>" value="always" class="tog" <?php echo ($show_map == 'always' ? 'checked="checked"' : '');?>>Always
						</label>
					</p>
					<p class="radio_option">
						<label>
							<input type="radio" name="<?php echo esc_attr( $this->get_field_name('show_map') ); ?>" value="never" class="tog" <?php echo ($show_map == 'never' ? 'checked="checked"' : '');?>>Never
						</label>
					</p>
					<p class="radio_option">
						<label>
							<input type="radio" name="<?php echo esc_attr( $this->get_field_name('show_map') ); ?>" value="per_location" class="tog" <?php echo ($show_map == 'per_location' ? 'checked="checked"' : '');?>>Use Individual Location's Setting
						</label>
					</p>
				</div>
			</fieldset>
			
		</div>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['show_location_image'] = $new_instance['show_location_image'];
		$instance['style'] = $new_instance['style'];
		$instance['category'] = $new_instance['category'];
		$instance['show_phone'] = $new_instance['show_phone'];
		$instance['show_fax'] = $new_instance['show_fax'];
		$instance['show_email'] = $new_instance['show_email'];
		$instance['show_info'] = $new_instance['show_info'];
		$instance['show_map'] = $new_instance['show_map'];
		$instance['order'] = $new_instance['order'];
		$instance['order_by'] = $new_instance['order_by'];
		
		return $instance;
	}

	function widget($args, $instance)
	{
		$gp_lp = new LocationsPlugin();
		
		extract($args, EXTR_SKIP);
		echo wp_kses($before_widget, 'post');

		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$show_location_image = empty($instance['show_location_image']) ? false : $instance['show_location_image'];
		$style = empty($instance['style']) ? 'small' : $instance['style'];
		$caption = empty($instance['caption']) ? '' : $instance['caption'];
		$category = empty($instance['category']) ? '' : $instance['category'];
		$show_phone = empty($instance['show_phone']) ? true : $instance['show_phone'];
		$show_fax = empty($instance['show_fax']) ? true : $instance['show_fax'];
		$show_email = empty($instance['show_email']) ? true : $instance['show_email'];
		$show_info = empty($instance['show_info']) ? true : $instance['show_info'];
		$show_map = empty($instance['show_map']) ? 'per_location' : $instance['show_map'];
		$order = empty($instance['order']) ? 'ASC' : $instance['order'];
		$order_by = empty($instance['order_by']) ? 'title' : $instance['order_by'];
		
		if (!empty($title)){
			echo wp_kses($before_title, 'post');
			echo wp_kses($title, 'strip');
			echo wp_kses($after_title, 'post');
		}
		
		$sc_output = $gp_lp->locations_shortcode(
			array(
				'show_photos' => $show_location_image,
				'caption' => $caption,
				'style' => $style,
				'category' => $category,
				'show_phone' => $show_phone,
				'show_fax' => $show_fax,
				'show_email' => $show_email,
				'show_info' => $show_info,
				'show_map' => $show_map,
				'order' => $order,
				'orderby' => $order_by
			)
		);
		echo wp_kses( $sc_output, 'post' );
		echo wp_kses( $after_widget, 'post' );
	} 
}