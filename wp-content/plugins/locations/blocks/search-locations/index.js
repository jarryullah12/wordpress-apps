( 
	function( wp ) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://github.com/WordPress/gutenberg/tree/master/blocks#api
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://github.com/WordPress/gutenberg/tree/master/element#element
	 */
	var el = wp.element.createElement;
	/**
	 * Retrieves the translation of text.
	 * @see https://github.com/WordPress/gutenberg/tree/master/i18n#api
	 */
	var __ = wp.i18n.__;

	var decode_text = function(txt) {
		return jQuery('<textarea />').html(txt).text();
	};
	
	var build_post_options = function(posts) {
		var opts = [
			{
				label: 'Select a Location',
				value: ''
			}
		];

		// build list of options from goals
		for( var i in posts ) {
			post = posts[i];
			opts.push( 
			{
				label: decode_text(post.title.rendered),
				value: post.id
			});
		}
		return opts;
	};	

	var build_category_options = function(categories) {
		var opts = [
			{
				label: 'All Categories',
				value: ''
			}
		];

		// build list of options from goals
		for( var i in categories ) {
			cat = categories[i];
			opts.push( 
			{
				label: cat.name,
				value: cat.id
			});
		}
		return opts;
	};	
	
	var extract_label_from_options = function (opts, val) {
		var label = '';
		for (j in opts) {
			if ( opts[j].value == val ) {
				label = opts[j].label;
				break;
			}										
		}
		return label;
	};
	
	var checkbox_control = function (label, checked, onChangeFn) {
		// add checkboxes for which fields to display
		var controlOptions = {
			checked: checked,
			label: label,
			value: '1',
			onChange: onChangeFn,
		};	
		return el(  wp.components.CheckboxControl, controlOptions );
	};

	var text_control = function (label, value, className, onChangeFn) {
		var controlOptions = {
			label: label,
			value: value,
			className: className,
			onChange: onChangeFn,
		};
		return el(  wp.components.TextControl, controlOptions );
	};

	var radio_control = function (label, value, options, className, onChangeFn) {
		var controlOptions = {
			label: label,
			onChange: onChangeFn,
			options: options,
			selected: value,
			className: '',
		};
		return el(  wp.components.RadioControl, controlOptions );
	};
	
	var iconGroup = [];
	iconGroup.push(	el(
			'path',
			{ d: "M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"}
		)
	);
	iconGroup.push(	el(
			'path',
			{ d: "M0 0h24v24H0z", fill: 'none' }
		)
	);

	var iconEl = el(
		'svg', 
		{ width: 24, height: 24 },
		iconGroup
	);
	
	registerBlockType( 'locations/search-locations', {
		title: __( 'Search Locations' ),
		category: 'locations-plugin',
		supports: {
			html: false,
		},
		edit: wp.data.withSelect( function( select ) {
					return {
						categories: select( 'core' ).getEntityRecords( 'taxonomy', 'location-categories', {
							order: 'asc',
							orderby: 'id'
						})
					};
				} ) ( function( props ) {
						var retval = [];
						var inspector_controls = [],
							title = props.attributes.title || '',
							category = props.attributes.category || '',
							map_width = props.attributes.map_width || '',
							map_height = props.attributes.map_height || '',
							order = props.attributes.order || '',
							orderby = props.attributes.orderby || '',
							caption = props.attributes.caption || '',
							search_button_label = props.attributes.search_button_label || '',
							search_input_label = props.attributes.search_input_label || '',
							search_again_label = props.attributes.search_again_label || '',
							category_select_label = props.attributes.category_select_label || '',
							category_select_description = props.attributes.category_select_description || '',
							radius_select_label = props.attributes.orderby || '',
							search_box_position = props.attributes.search_box_position || 'above',
							search_results_style = typeof(props.attributes.search_results_style) != 'undefined' ? props.attributes.search_results_style : '',
							show_category_select = typeof(props.attributes.show_category_select) != 'undefined' ? props.attributes.show_category_select : false,
							allow_multiple_categories = typeof(props.attributes.allow_multiple_categories) != 'undefined' ? props.attributes.allow_multiple_categories : true,
							show_search_radius = typeof(props.attributes.show_search_radius) != 'undefined' ? props.attributes.show_search_radius : false,
							show_search_results = typeof(props.attributes.show_search_results) != 'undefined' ? props.attributes.show_search_results : true,
							
							default_latitude = props.attributes.default_latitude || '',
							default_longitude = props.attributes.default_longitude || '',
							caption_class = props.attributes.caption_class || '',
							id = props.attributes.id || '',
							form_class = props.attributes['class'] || '',
							map_class = props.attributes.map_class || '',
							search_button_class = props.attributes.search_button_class || '',
							input_wrapper_class = props.attributes.input_wrapper_class || '',
							search_input_id = props.attributes.search_input_id || '',
							search_input_class = props.attributes.search_input_class || '',
							search_again_class = props.attributes.search_again_class || '',
							category_select_id = props.attributes.category_select_id || '',
							radius_select_id = props.attributes.radius_select_id || '',

							show_phone = typeof(props.attributes.show_phone) != 'undefined' ? props.attributes.show_phone : true,
							show_fax = typeof(props.attributes.show_fax) != 'undefined' ? props.attributes.show_fax : true,
							show_email = typeof(props.attributes.show_email) != 'undefined' ? props.attributes.show_email : true,
							default_locations_to_show = typeof(props.attributes.default_locations_to_show) != 'undefined' ? props.attributes.default_locations_to_show : 'none',
							show_info = typeof(props.attributes.show_info) != 'undefined' ? props.attributes.show_info : true,
							show_map = typeof(props.attributes.show_map) != 'undefined' ? props.attributes.show_map : 'per_location',
							show_location_image = typeof(props.attributes.show_location_image) != 'undefined' ? props.attributes.show_location_image : false,								
							force_single_mode = false,
							focus = props.isSelected;

						var before_search_fields = [];							
						var before_search_options = [
							{
								label: __('All'),
								value: 'all',
							},
							{
								label: __('Nearby'),
								value: 'nearby',
							},
							{
								label: __('None'),
								value: 'none',
							},

						];							

						before_search_fields.push( 
							radio_control( __('Locations to show before search:'), default_locations_to_show, before_search_options, 'display_maps_radio', function( newVal ) {
								props.setAttributes({
									default_locations_to_show: newVal,
								});
							})
						);


						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Initial Locations To Display'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, before_search_fields)
							)
						);
						
						/* Map Dimensions fields */
						var map_dimensions_fields = [];							
						map_dimensions_fields.push( 
							text_control( __('Map Width:'), map_width, 'map_width', function( newVal ) {
								props.setAttributes({
									map_width: newVal,
								});
							})
						);

						map_dimensions_fields.push( 
							text_control( __('Map Height:'), map_height, 'map_height', function( newVal ) {
								props.setAttributes({
									map_height: newVal,
								});
							})
						);
						
						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Map Dimensions'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, map_dimensions_fields)
							)
						);
						
						// end Map Dimensions
						
						/* Search Form Options fields */
						var search_form_options_fields = [];							
						search_form_options_fields.push( 
							checkbox_control( __('Show Category Select'), show_category_select, function( newVal ) {
								props.setAttributes({
									show_category_select: newVal,
								});
							})
						);

						search_form_options_fields.push( 
							checkbox_control( __('Allow Multiple Categories'), allow_multiple_categories, function( newVal ) {
								props.setAttributes({
									allow_multiple_categories: newVal,
								});
							})
						);
						
						search_form_options_fields.push( 
							checkbox_control( __('Show Search Radius'), show_search_radius, function( newVal ) {
								props.setAttributes({
									show_search_radius: newVal,
								});
							})
						);
						
						search_form_options_fields.push( 
							checkbox_control( __('Show Search Results'), show_search_results, function( newVal ) {
								props.setAttributes({
									show_search_results: newVal,
								});
							})
						);
						
						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Search Form Options'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, search_form_options_fields)
							)
						);
						
						// end Search Form Options
						
						/* Search Box Position */
						
						var search_box_position_fields = [];							
						var search_box_position_options = [
							{
								label: __('Above Results'),
								value: 'above',
							},
							{
								label: __('Below Results'),
								value: 'below',
							},

						];							

						search_box_position_fields.push( 
							radio_control( __('Search box position:'), search_box_position, search_box_position_options, 'search_box_position_radio', function( newVal ) {
								props.setAttributes({
									search_box_position: newVal,
								});
							})
						);

						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Search Box Position'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, search_box_position_fields)
							)
						);
						
						// end Search Box Position
						
						/* Labels and Descriptions fields */
						var labels_fields = [];							
						labels_fields.push( 
							text_control( __('Caption:'), caption, 'caption', function( newVal ) {
								props.setAttributes({
									caption: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Search Button Label:'), search_button_label, 'search_button_label', function( newVal ) {
								props.setAttributes({
									search_button_label: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Search Input Label:'), search_input_label, 'search_input_label', function( newVal ) {
								props.setAttributes({
									search_input_label: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Search Again Label:'), search_again_label, 'search_again_label', function( newVal ) {
								props.setAttributes({
									search_again_label: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Category Select Label:'), category_select_label, 'category_select_label', function( newVal ) {
								props.setAttributes({
									category_select_label: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Category Select Description:'), category_select_description, 'category_select_description', function( newVal ) {
								props.setAttributes({
									category_select_description: newVal,
								});
							})
						);

						labels_fields.push( 
							text_control( __('Radius Select Label:'), radius_select_label, 'radius_select_label', function( newVal ) {
								props.setAttributes({
									radius_select_label: newVal,
								});
							})
						);

						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Labels and Descriptions'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, labels_fields)
							)
						);

						// end Labels and Descriptions
						
						/* Search Results Style fields */
						var search_results_style_fields = [];							
						var search_results_style_options = [
							{
								label: __('Standard'),
								value: '',
							},
							{
								label: __('Tile'),
								value: 'tile',
							},
						];							

						search_results_style_fields.push( 
							radio_control( __('Search results style:'), search_results_style, search_results_style_options, 'display_maps_radio', function( newVal ) {
								props.setAttributes({
									search_results_style: newVal,
								});
							})
						);


						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Search Results Style'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, search_results_style_fields)
							)
						);
						// end Search Results Style 

						/* Advanced Options fields */
						var advanced_options_fields = [];							
						advanced_options_fields.push( 
							text_control( __('Default Latitude:'), default_latitude, 'default_latitude', function( newVal ) {
								props.setAttributes({
									default_latitude: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Default Longitude:'), default_longitude, 'default_longitude', function( newVal ) {
								props.setAttributes({
									default_longitude: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Caption Class:'), caption_class, 'caption_class', function( newVal ) {
								props.setAttributes({
									caption_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Form ID:'), id, 'form_id', function( newVal ) {
								props.setAttributes({
									id: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Form Class:'), form_class, 'form_class', function( newVal ) {
								props.setAttributes({
									'class': newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Map Class:'), map_class, 'map_class', function( newVal ) {
								props.setAttributes({
									map_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Search Button Class:'), search_button_class, 'search_button_class', function( newVal ) {
								props.setAttributes({
									search_button_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Input Wrapper Class:'), input_wrapper_class, 'input_wrapper_class', function( newVal ) {
								props.setAttributes({
									input_wrapper_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Search Input ID:'), search_input_id, 'search_input_id', function( newVal ) {
								props.setAttributes({
									search_input_id: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Search Input Class:'), search_input_class, 'search_input_class', function( newVal ) {
								props.setAttributes({
									search_input_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Search Again Class:'), search_again_class, 'search_again_class', function( newVal ) {
								props.setAttributes({
									search_again_class: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Category Select ID:'), category_select_id, 'category_select_id', function( newVal ) {
								props.setAttributes({
									category_select_id: newVal,
								});
							})
						);

						advanced_options_fields.push( 
							text_control( __('Radius Select ID:'), radius_select_id, 'radius_select_id', function( newVal ) {
								props.setAttributes({
									radius_select_id: newVal,
								});
							})
						);

						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Advanced Options'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								el('div', { className: 'janus_editor_field_group' }, advanced_options_fields)
							)
						);
						// end Advanced Options						

						// add all inspector controls to the return value
						retval.push(
							el( wp.editor.InspectorControls, {}, inspector_controls ) 
						);


						var inner_fields = [];
						inner_fields.push( el('h3', { className: 'block-heading' }, 'Locations - Search Locations') );
						inner_fields.push( el('blockquote', {}, 'A form to search your Locations.') );
						retval.push( el('div', {'className': 'locations-editor-not-selected'}, inner_fields ) );

					
						return el( 'div', { className: 'locations-single-location-editor'}, retval );
			} ),
		save: function() {
			return null;
		},
		attributes: {
			default_locations_to_show: {
				type: 'string',
			},
			category: {
				type: 'string',
			},			
			map_width: {
				type: 'string',
			},			
			map_height: {
				type: 'string',
			},			
			title: {
				type: 'string',
			},			
			search_box_position: {
				type: 'string',
			},			
			show_category_select: {
				type: 'boolean',
			},			
			allow_multiple_categories: {
				type: 'boolean',
			},			
			show_search_radius: {
				type: 'boolean',
			},			
			show_search_results: {
				type: 'boolean',
			},			
			show_phone: {
				type: 'boolean',
			},			
			show_fax: {
				type: 'boolean',
			},			
			show_email: {
				type: 'boolean',
			},			
			show_info: {
				type: 'boolean',
			},			
			show_map: {
				type: 'string',
			},			
			show_location_image: {
				type: 'boolean',
			},
			force_single_mode: {
				type: 'boolean',
			},
			caption: {
				type: 'string',
			},
			search_button_label: {
				type: 'string',
			},
			search_input_label: {
				type: 'string',
			},
			search_again_label: {
				type: 'string',
			},
			category_select_label: {
				type: 'string',
			},
			category_select_description: {
				type: 'string',
			},
			radius_select_label: {
				type: 'string',
			},
			default_latitude: {
				type: 'string',
			},
			default_longitude: {
				type: 'string',
			},
			caption_class: {
				type: 'string',
			},
			id: {
				type: 'string',
			},
			'class': {
				type: 'string',
			},
			map_class: {
				type: 'string',
			},
			search_button_class: {
				type: 'string',
			},
			input_wrapper_class: {
				type: 'string',
			},
			search_input_id: {
				type: 'string',
			},
			search_input_class: {
				type: 'string',
			},
			search_again_class: {
				type: 'string',
			},
			category_select_id: {
				type: 'string',
			},
			radius_select_id: {
				type: 'string',
			},			
		},
		icon: iconEl,
	} );
} )(
	window.wp
);
