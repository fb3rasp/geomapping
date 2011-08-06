var infoControl = null;

/** 
 * This script implements the mouse-click event to retrieve information
 * from WMS layers.
 */
(function($) {
	$.entwine('ol', function($) {

		$('.olMap').entwine({

			/**
			 * Iterate through the layer-config array and return the unique
			 * list of all queryable layers, which is used to perform the 
			 * getfeatureinfo request one.
			 */ 
			getDistinctLayerURLList: function(mapConfig) {
				var layers = this.getOLMap().layers;
				var list = Array();

				// create distinct list or layer urls
				var index   = 0;
				for (var i in layers) {
					layer = layers[i];
					if (layer.queryable == true) list[index++] = layer.url; 
				};
				return list;				
			},
						
			augmentMap: function(map) {
			// initMap: function() {
				var self = this;
				this._super(map);
				
				this.attachPopup();
			},

			/**
			 * @todo: populate the url  ('Feature/dogetfeatureinfo') via the 
			 * framework to make it flexible.
			 */
			attachPopup: function() {
				var self = this;
				var layerUrls = this.getDistinctLayerURLList();
				
				infoControl = new OpenLayers.Control.GeoserverGetFeatureInfo({
					url: 'Feature/dogetfeatureinfo', 
					layerUrls: layerUrls,
					title: 'Identify features by clicking',
					queryVisible: true,
					eventListeners: {
						beforegetfeatureinfo: function(xy){
							// show popup just when layers are queriable.
							if (this.findLayers().length > 0) {
								self.showPopupXY(xy);
							}
						},
						getfeatureinfo: function(event) {
							self.showFeatureOnPopup(event);
						}
						
					}			
				});
				this.getOLMap().addControl(infoControl);
				infoControl.activate();
			}
		});
	});
}(jQuery));	
