<div class="store_locator_info_window">
	<h3 id="firstHeading" class="firstHeading store_locator_location_heading">
		<a href="{{permalink}}" target="_blank">{{title}}</a>
	</h3>
	<div id="bodyContent">
		<p class="addr">
			{{street_address}}
			{{#street_address_line_2}}
			<br />{{street_address_line_2}}
			{{/street_address_line_2}}
			<br />
			{{city}}, {{state}} {{zipcode}}
		</p>
		{{#phone}}
		<p class="phone"><strong>Phone:</strong> {{phone}}</p>
		{{/phone}}
		{{#fax}}
		<p class="fax"><strong>Fax:</strong> {{fax}}</p>
		{{/fax}}
		{{#email}}
		<p class="email"><strong>Email:</strong> <a href="mailto:{{email}}">{{email}}</a></p>
		{{/email}}
		{{#directions_url}}
		<p class="directions"><a href="{{directions_url}}" target="_blank">{{directions_label}}</a></p>
		{{/directions_url}}
	</div>
</div>