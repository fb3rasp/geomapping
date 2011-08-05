(function($) { 
	$.entwine('ol', function($) {	
		
		$('.olMap').entwine({ 

			ControllerName: 'Feature', 
			<% control Map %>
 			MapID: '$ID', 
			
			Resolutions : $GetResolutionsAsJSON,
			
			DisplayProjection : new OpenLayers.Projection('$DisplayProjection'),
			
			Projection : new OpenLayers.Projection('$Projection'),
			
			<% end_control %>
			initLayers: function() {
				var self = this;
				<% control Map %>$JavaScript<% end_control %>
			},

			loadConfiguration: function() {
				<% control Map %>
				var map = this.getOLMap();

				var center = new OpenLayers.LonLat($Long, $Lat);
				var center = center.transform(map.displayProjection, map.projection);
				
		 		map.setCenter(center, $ZoomLevel );

				OpenLayers.ProxyHost="Proxy/dorequest?u=";
				<% end_control %>
			}
		});
	});

}(jQuery));



