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
	
	registerBlockType( 'locations/all-locations', {
		title: __( 'All Locations' ),
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
							order = props.attributes.order || '',
							orderby = props.attributes.orderby || '',
							show_phone = typeof(props.attributes.show_phone) != 'undefined' ? props.attributes.show_phone : true,
							show_fax = typeof(props.attributes.show_fax) != 'undefined' ? props.attributes.show_fax : true,
							show_email = typeof(props.attributes.show_email) != 'undefined' ? props.attributes.show_email : true,
							show_info = typeof(props.attributes.show_info) != 'undefined' ? props.attributes.show_info : true,
							show_map = typeof(props.attributes.show_map) != 'undefined' ? props.attributes.show_map : 'per_location',
							show_location_image = typeof(props.attributes.show_location_image) != 'undefined' ? props.attributes.show_location_image : false,								
							force_single_mode = false,
							focus = props.isSelected;

					var category_fields = [];
				
					// add <select> to choose the Category
					var controlOptions = {
						label: __('Select a Category:'),
						value: category,
						onChange: function( newVal ) {
							props.setAttributes({
								category: newVal
							});
						},
						options: build_category_options(props.categories),
					};
				
					category_fields.push(
						el(  wp.components.SelectControl, controlOptions )
					);

					var orderby_opts = [
						{
							label: 'Title',
							value: 'title',
						},
						{
							label: 'Random',
							value: 'rand',
						},
						{
							label: 'ID',
							value: 'id',
						},
						{
							label: 'Author',
							value: 'author',
						},
						{
							label: 'Name',
							value: 'name',
						},
						{
							label: 'Date',
							value: 'date',
						},
						{
							label: 'Last Modified',
							value: 'last_modified',
						},
						{
							label: 'Parent ID',
							value: 'parent_id',
						},
					];

					// add <select> to choose the Order By Field
					var controlOptions = {
						label: __('Order By:'),
						value: orderby,
						onChange: function( newVal ) {
							props.setAttributes({
								orderby: newVal
							});
						},
						options: orderby_opts,
					};
				
					category_fields.push(
						el(  wp.components.SelectControl, controlOptions )
					);

					var order_opts = [
						{
							label: 'Ascending (A-Z)',
							value: 'asc',
						},
						{
							label: 'Descending (Z-A)',
							value: 'desc',
						},
					];

					// add <select> to choose the Order (asc, desc)
					var controlOptions = {
						label: __('Order:'),
						value: order,
						onChange: function( newVal ) {
							props.setAttributes({
								order: newVal
							});
						},
						options: order_opts,
					};
				
					category_fields.push(
						el(  wp.components.SelectControl, controlOptions )
					);
					
					inspector_controls.push(							
						el (
							wp.components.PanelBody,
							{
								title: __('Category'),
								className: 'gp-panel-body',
								initialOpen: false,
							},
							category_fields
						)
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
					
					var map_display_fields = [];							
					var map_display_options = [
						{
							label: __('Always'),
							value: 'always',
						},
						{
							label: __('Never'),
							value: 'never',
						},
						{
							label: __('Use Individual Location\'s Setting'),
							value: 'per_location',
						},
					
					];							
					map_display_fields.push( 
						radio_control( __('Show Map:'), show_map, map_display_options, 'display_maps_radio', function( newVal ) {
							props.setAttributes({
								show_map: newVal,
							});
						})
					);
					
					
					inspector_controls.push( 
						el (
							wp.components.PanelBody,
							{
								title: __('Map Display'),
								className: 'gp-panel-body',
								initialOpen: true,
							},
							el('div', { className: 'janus_editor_field_group' }, map_display_fields)
						)
					);


					// add all inspector controls to the return value
					retval.push(
						el( wp.editor.InspectorControls, {}, inspector_controls ) 
					);


				var inner_fields = [];
				inner_fields.push( el('h3', { className: 'block-heading' }, 'Locations - All Locations') );
				inner_fields.push( el('blockquote', {}, 'A list of Locations from your database.') );
				retval.push( el('div', {'className': 'locations-editor-not-selected'}, inner_fields ) );

						
				return el( 'div', { className: 'locations-single-location-editor'}, retval );
			} ),
		save: function() {
			return null;
		},
		attributes: {
			id: {
				type: 'string',
			},
			category: {
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
				type: 'string',
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
