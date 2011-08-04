
(function($) {
	$.entwine('ol', function($) {
		$('.olMap').entwine({
			
			OLMap: null,
			
			// Only applies to Google layers, which in turn set the map to OpenLayers.FixedZoomLevels
			MinZoomLevel: 3, 
			
			createControls: function(map) {
				map.addControl(new OpenLayers.Control.Navigation());
				map.addControl(new OpenLayers.Control.PanZoomBar());
				map.addControl(new OpenLayers.Control.KeyboardDefaults());
				map.addControl(new OpenLayers.Control.MousePosition());
			},
			
			/**
			 * Initialise the open layers map instance and uses a div object which
			 * must exist in the DOM. 
			 *
			 * @param string divMap name of the target div object
			 **/
			initMap: function() {

				var self = this;
				
				OpenLayers.ProxyHost="Proxy/dorequest?u=";
				var mapOptions = { 
					
					resolutions: this.getResolutions(),

					displayProjection: this.getDisplayProjection(),

					projection: this.getProjection(),
					
					// 26 mappings, but only the first 22 will apply for Google Maps base layers
					// resolutions: [
					// 	156543.03390625, 78271.516953125, 39135.7584765625, 19567.87923828125, 9783.939619140625, 4891.9698095703125, 2445.9849047851562, 1222.9924523925781, 611.4962261962891, 305.74811309814453, 152.87405654907226, 76.43702827453613, 38.218514137268066, 19.109257068634033, 9.554628534317017, 4.777314267158508, 2.388657133579254, 1.194328566789627, 0.5971642833948135, 0.29858214169740677, 0.14929107084870338, 0.07464553542435169, 0.037322767712175846, 0.018661383856087923, 0.009330691928043961, 0.004665345964021981
					// ],
					// projection: new OpenLayers.Projection('EPSG:900913'),
					// maxExtent: new OpenLayers.Bounds(-2.003750834E7,-2.003750834E7,2.003750834E7,2.003750834E7),
					units: "meters",
					controls: []
				};
				
				var map = new OpenLayers.Map(this[0], mapOptions);
				
				this.createControls(map);

				this.setOLMap(map);
				
				this.initLayers();
				this.loadConfiguration();

				this.getOLMap().paddingForPopups = new OpenLayers.Bounds(20, 40, 20, 20);				
				
				this.trigger('oninit');
			},
			
			redraw: function() {
				// might be called before map is properly initialized
				if(this.getOLMap()) this.getOLMap().updateSize();
			},

			loadStart: function(evt) {
				$('.ajaxLoading').removeClass('ajaxLoading_off');
				$('.ajaxLoading').addClass('ajaxLoading_on');
			},

			loadEnd: function(evt) {
				$('.initmap').removeClass('initmap');
				$('.ajaxLoading').addClass('ajaxLoading_off');
				$('.ajaxLoading').removeClass('ajaxLoading_on');
			},
			
		});
	});


	// Fired on DOMload
	$(function(){
		$('.olMap').entwine('ol').initMap();
	});
}(jQuery));


