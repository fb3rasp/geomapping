
/** 
 * This script implements the mouse-click event to retrieve information
 * from WFS layers.
 */
(function($) {
	$.entwine('ol', function($) {

		$('.olMap').entwine({

			SelectedFeature: null,

			augmentMap: function(map) {
				var self = this;
				this._super(map);

				//
				// enable all vector layers to be selectable via one controller.
				var layers = map.getBy('layers','isVector',true);
				this.activateLayers(layers);
			},

			/** 
			 * Enable select/unselect controllers for WFS/Vector layers.
			 */
			activateLayers: function(layers) {
				var self = this;
				
				// Create a select feature control and add it to the map.
				var hovercontrol = new OpenLayers.Control.SelectFeature(layers, 
					{
						hover: true,
						highlightOnly: true,
						renderIntent: "temporary" 			
					}
				);

				var selectcontrol = new OpenLayers.Control.SelectFeature(layers, 
					{
						onSelect: function(feature) { return self.featureSelect(this, feature); }, 
						onUnselect: function(feature) { return self.featureUnselect(this, feature); }
					}
				);

				this.getOLMap().addControl(hovercontrol);
				hovercontrol.activate();

				this.getOLMap().addControl(selectcontrol);
				selectcontrol.activate();
			},

			/**
			 * Callback method, called when user clicks on a vector feature.
			 *
			 * @param the selected feature.
			 **/
			featureSelect: function(control, feature) {
				if(!feature.layer.options.queryable || feature.layer.options.queryable === false) return false;
				
				var self = this;				
				var event = control.handlers.feature.evt;
				control.handlers.feature.evt.suck = true;
				OpenLayers.Event.stop(event);
				
				this.setSelectedFeature( feature );

				var featureIDList = new Array();
				if (feature.cluster) {
					for (var i=0, len=feature.cluster.length; i<len; ++i) {
						featureIDList.push(feature.cluster[i].fid);
					}
				} else{
					featureIDList.push(feature.fid);
				}

				var pos = feature.geometry.getBounds().getCenterLonLat();
				var closeBoxCallback = function() { self.featureUnselect(); };

				this.showPopup(pos,closeBoxCallback);

				// prepare request for AJAX 
				var mapID = this.getMapID();
				var url = this.getControllerName() + '/dogetfeature/'+mapID+'/'+featureIDList;

				// get attributes for selected feature(s)
				var loadCallback = function(response) { self.loadPopup(response); };

				OpenLayers.loadURL(url, null, this, loadCallback);
				return false;
			},

			/**
			 * @param the selected feature.
			 **/
			featureUnselect: function(feature) {
				var popup = this.getOLPopup();
				if(popup !== null){
					this.closePopup();
				}
			}

		});
	});
}(jQuery));