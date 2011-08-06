/**
 * 
 */
OpenLayers.Control.GeoserverGetFeatureInfo = OpenLayers.Class(OpenLayers.Control.WMSGetFeatureInfo, {

   /** 
	 * NOTE: I need to overwrite urlMatches to ensure that openlayers does not
	 * fail because of injected http parameters.
	 * We experienced issues with the fullscreen view because we add the list
	 * of visible layers as a parameter which caused comparision issues later in
	 * the get-feature-request.
	 *
     * Method: urlMatches
     * Test to see if the provided url matches either the control <url> or one
     *     of the <layerUrls>.
     *
     * Parameters:
     * url - {String} The url to test.
     *
     * Returns:
     * {Boolean} The provided url matches the control <url> or one of the
     *     <layerUrls>.
     */
    urlMatches: function(url) {
        var matches = OpenLayers.Util.isEquivalentUrl(this.url, url);

        if(!matches && this.layerUrls) {
            for(var i=0, len=this.layerUrls.length; i<len; ++i) {
                if(this.isEquivalentUrl(this.layerUrls[i], url)) {
                    matches = true;
                    break;
                }
            }
        }
        return matches;
    },

	/** 
	 * Function: isEquivalentUrl
	 * Test two URLs for equivalence. 
	 * 
	 * Setting 'ignoreCase' allows for case-independent comparison.
	 * 
	 * Comparison is based on: 
	 *  - Protocol
	 *  - Host (evaluated without the port)
	 *  - Port (set 'ignorePort80' to ignore "80" values)
	 *  - Hash ( set 'ignoreHash' to disable)
	 *  - Pathname (for relative <-> absolute comparison) 
	 *  - Arguments (so they can be out of order)
	 *  
	 * Parameters:
	 * url1 - {String}
	 * url2 - {String}
	 * options - {Object} Allows for customization of comparison:
	 *                    'ignoreCase' - Default is True
	 *                    'ignorePort80' - Default is True
	 *                    'ignoreHash' - Default is True
	 *
	 * Returns:
	 * {Boolean} Whether or not the two URLs are equivalent
	 */
	isEquivalentUrl: function(url1, url2, options) {
	    options = options || {};

	    OpenLayers.Util.applyDefaults(options, {
	        ignoreCase: true,
	        ignorePort80: true,
	        ignoreHash: true
	    });

	    if(url1) var urlObj1 = OpenLayers.Util.createUrlObject(url1, options);
	    if(url2) var urlObj2 = OpenLayers.Util.createUrlObject(url2, options);

		if (urlObj1 == undefined) {
			return false;
		}

		if (urlObj2 == undefined) {
			return false;
		}

	    //compare all keys except for "args" (treated below)
	    for(var key in urlObj1) {
	        if(key !== "args") {
	            if(urlObj1[key] != urlObj2[key]) {
	                return false;
	            }
			}
	    }

	    // compare search args - irrespective of order
	    for(var key in urlObj1.args) {
			// Skip layers parameters, a parameter which has been injected by
			// the SilverStripe CMS. Other option would be to run a array comparision
			// of the layer-parameters to ensure that both urls are equivalent.
			// But we can assume that those requests are equal in regards of the 
			// parameters because both uses the same http request to generate the
			// url-object.
	        if(key !== "layers") {
		        if(urlObj1.args[key] != urlObj2.args[key]) {
		            return false;
		        }
			}
	        delete urlObj2.args[key];
	    }
	    // urlObj2 shouldn't have any args left
	    for(var key in urlObj2.args) {
	        return false;
	    }
	    return true;
	},
	
   /**
     * Method: buildWMSOptions
     * Build an object with the relevant WMS options for the GetFeatureInfo request
     *
     * Parameters:
     * url - {String} The url to be used for sending the request
     * layers - {Array(<OpenLayers.Layer.WMS)} An array of layers
     * clickPosition - {<OpenLayers.Pixel>} The position on the map where the mouse
     *     event occurred.
     * format - {String} The format from the corresponding GetMap request
     */
    buildWMSOptions: function(url, layers, clickPosition, format) {
        var layerNames = [];
        for (var i = 0, len = layers.length; i < len; i++) { 
            layerNames = layerNames.concat(layers[i].name);
        }
        var params = OpenLayers.Util.extend({
            layers: layerNames,
            bbox: this.map.getExtent().toBBOX(null, layers[0].reverseAxisOrder()),
            height: this.map.getSize().h,
            width: this.map.getSize().w
        }, (parseFloat(layers[0].params.VERSION) >= 1.3) ?
            {
                crs: this.map.getProjection(),
                i: clickPosition.x,
                j: clickPosition.y
            } :
            {
                srs: this.map.getProjection(),
                x: clickPosition.x,
                y: clickPosition.y
            }
        );
        OpenLayers.Util.applyDefaults(params, this.vendorParams);
        return {
            url: url,
            params: OpenLayers.Util.upperCaseObject(params),
            callback: function(request) {
                this.handleResponse(clickPosition, request);
            },
            scope: this
        };
    }
});