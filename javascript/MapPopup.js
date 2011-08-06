
(function($) {
	$.entwine('ol', function($) {

		$('.olMap').entwine({

			OLPopup: null,
			
			OLPopupSize: null,

			ControllerName: null,

			augmentMap: function(map) {
			// initMap: function() {
				var self = this;
				this._super(map);
				
				this.setOLPopup(null);
				this.setOLPopupSize( new OpenLayers.Size(350,300) );
				
				// when the user zooms in or out, remove the bubble.
				map.events.register("movestart", map, function(e) { 
					OpenLayers.Event.stop(e);
					return false;
				} );

				map.events.register("zoomend", map, function(evt) { 
					self.closePopup(evt); 
				} );
			},

			/**
			 * Open the map info popup window.
			 *
			 * @param latlon <OpenLayers.LonLat>} location of the popup-window.
 			 * @param closeBoxCallback - {Function} 
			 */
			showPopup: function(latlon, closeBoxCallback) {
				var self     = this;				
				var map      = this.getOLMap();
				
				// close existing popup
				this.closePopup();

				if (!closeBoxCallback) {
					closeBoxCallback = function() { self.closePopup(); }; 
				}
				
				// popup temporary content while we load data...
				var htmlText = "<div class='featureInfoContent'><img src='geoviewer/images/ajax-loader.gif' alt='loading'/>&nbsp;Loading please wait...</div>";

				// initiate a new popup
				popup = new OpenLayers.Popup("Information", 
					latlon, this.getOLPopupSize(), htmlText, true, closeBoxCallback
				);
				
				popup.panMapIfOutOfView = true;
				popup.setOpacity(0.95);

				map.addPopup(popup);
				this.setOLPopup(popup);
			},
			
			/**
			 * Open the map info popup window.
			 *
			 * @param xy mouse position, relative to viewport position
			 */
			showPopupXY: function(xy, callback) {
				var map = this.getOLMap();
				var latlon = map.getLonLatFromViewPortPx(xy.xy);
				
				return this.showPopup(latlon, callback);
			},
			
			showFeatureOnPopup: function(evt) {
				if (popup != null) {
					popup.setContentHTML( evt.text );
					$(".featureInfoContent li:nth-child(odd)", $("#"+popup.id)).addClass("odd");
					OpenLayers.Event.stop(evt);
				}
			},
			
			closePopup: function() {
				var popup = this.getOLPopup();

				// close existing popup
				if (popup != null) {
					popup.hide();
					this.getOLMap().removePopup(popup);
					this.setOLPopup(null);
					popup.destroy();
				}
			},


			/**
			 * Shows the response of the AJAX call in the popup-bubble on the map if 
			 * available.
			 *
 			 * @param response - {XMLHttpRequest} 
			 */
			loadPopup: function(response) {
				var popup = this.getOLPopup();

				if (popup != null) {
					if (response) {
						innerHTML = response.responseText;
						popup.setContentHTML( innerHTML );
					}
				}
			}			
		});
	});
	
}(jQuery));	
