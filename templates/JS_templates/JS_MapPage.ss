(function($) { 
	$.entwine('ol', function($) {	
		
		$('.olMap').entwine({ 

			ControllerName: 'Feature', 
			<% control Map %>
 			MapID: '$ID', 
			
			Resolutions : $GetResolutionsAsJSON,
			
			Projection : new OpenLayers.Projection('$Projection'),
			
			<% end_control %>
			initLayers: function() {
				var self = this;
				<% control Map %>$JavaScript<% end_control %>
			},

			loadConfiguration: function() {
				<% control Map %>
				var map = this.getOLMap();
				map.setCenter(new OpenLayers.LonLat($Long, $Lat), $ZoomLevel );

				OpenLayers.ProxyHost="Proxy/dorequest?u=";
				<% end_control %>
			}
		});
	});

}(jQuery));



