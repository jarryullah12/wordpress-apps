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
	
	var get_theme_group_label = function(theme_group_key) {
		if ( typeof(locations_admin_single_faq.theme_group_labels[theme_group_key]) !== 'undefined' ) {
			return locations_admin_single_faq.theme_group_labels[theme_group_key];
		}
		return 'Themes';
	};	

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

	var get_theme_options = function() {
		var theme_opts = [];
		for( theme_group in locations_admin_single_faq.themes ) {
			for ( theme_name in locations_admin_single_faq.themes[theme_group] ) {
				// skip the fields which were meant as optgroup labels
				if ( theme_name == theme_group ) {
					continue;
				}
				theme_opts.push({
					label: locations_admin_single_faq.themes[theme_group][theme_name],
					value: theme_name,
				});				
			}
		}
		return theme_opts;
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
			{ d: "M0 0h24v24H0z", fill: 'none' }
		)
	);
	iconGroup.push(	el(
			'path',
			{ d: "M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"}
		)
	);
	
	var iconEl = el(
		'svg', 
		{ width: 24, height: 24 },
		iconGroup
	);
	
	registerBlockType( 'locations/single-location', {
		title: __( 'Single Location' ),
		category: 'locations-plugin',
		supports: {
			html: false,
		},
		edit: wp.data.withSelect( function( select ) {
					return {
						posts: select( 'core' ).getEntityRecords( 'postType', 'location' )
					};
				} ) ( function( props ) {
							var retval = [];
							var inspector_controls = [],
								id = props.attributes.id || '',
								title = props.attributes.title || '',
								show_phone = typeof(props.attributes.show_phone) != 'undefined' ? props.attributes.show_phone : true,
								show_fax = typeof(props.attributes.show_fax) != 'undefined' ? props.attributes.show_fax : true,
								show_email = typeof(props.attributes.show_email) != 'undefined' ? props.attributes.show_email : true,
								show_info = typeof(props.attributes.show_info) != 'undefined' ? props.attributes.show_info : true,
								show_map = typeof(props.attributes.show_map) != 'undefined' ? props.attributes.show_map : true,
								show_location_image = typeof(props.attributes.show_location_image) != 'undefined' ? props.attributes.show_location_image : false,								
								force_single_mode = true,
								focus = props.isSelected;

						props.setAttributes({
							force_single_mode: true,
						});
								
						if ( !! focus || ! id.length ) {
							
							retval.push( el('h3', { className: 'block-heading' }, __('Locations - Single Location') ) );
							
							// add <select> to choose the faq
							var opts = build_post_options(props.posts);
							var controlOptions = {
								label: __('Select a Location:'),
								value: id,
								onChange: function( newVal ) {
									title = extract_label_from_options(opts, newVal);
									props.setAttributes({
										id: newVal,
										title: title
									});
								},
								options: opts,
							};
						
							retval.push(
									el(  wp.components.SelectControl, controlOptions )
							);
							
							// add checkboxes for which fields to display
							var display_fields = [];							
							display_fields.push( 
								checkbox_control( __('Phone Number'), show_phone, function( newVal ) {
									props.setAttributes({
										show_phone: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Fax Number'), show_fax, function( newVal ) {
									props.setAttributes({
										show_fax: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Email'), show_email, function( newVal ) {
									props.setAttributes({
										show_email: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Info'), show_info, function( newVal ) {
									props.setAttributes({
										show_info: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Map'), show_map, function( newVal ) {
									props.setAttributes({
										show_map: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Photo'), show_location_image, function( newVal ) {
									props.setAttributes({
										show_location_image: newVal,
									});
								})
							);
							
							
							inspector_controls.push( 
								el (
									wp.components.PanelBody,
									{
										title: __('Display Fields'),
										className: 'gp-panel-body',
										initialOpen: true,
									},
									el('div', { className: 'janus_editor_field_group' }, display_fields)
								)
							);


							// add all inspector controls to the return value
							retval.push(
								el( wp.editor.InspectorControls, {}, inspector_controls ) 
							);

						}

						else {
							var inner_fields = [];
							inner_fields.push( el('h3', { className: 'block-heading' }, 'Locations - Single Location') );							
							inner_fields.push( el('blockquote', {}, title) );
							retval.push( el('div', {'className': 'locations-editor-not-selected'}, inner_fields ) );
						}
						
				return el( 'div', { className: 'locations-single-faq-editor'}, retval );
			} ),
		save: function() {
			return null;
		},
		attributes: {
			id: {
				type: 'string',
			},
			title: {
				type: 'string',
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
				type: 'boolean',
			},			
			show_location_image: {
				type: 'boolean',
			},
			force_single_mode: {
				type: 'boolean',
			},
		},
		icon: iconEl,
	} );
} )(
	window.wp
);
