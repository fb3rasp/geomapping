var layer = null;
var options = [];

// current assumption: if google maps layers are used, 
// we always use sphericalMercator.
layer = new OpenLayers.Layer.Google(
	"$GMapTypeName",
	$.extend({
		type: $GMapType, sphericalMercator: true, numZoomLevels: 22
	}, options, {})
);

layer.queryable = false;
layer.ssid = $ID;

this.getOLMap().addLayer(layer);

layer.setVisibility($isVisible);	
<% if Visible %>this.getOLMap().setBaseLayer(layer, false);<% end_if %>

this.getOLMap().setLayerZIndex(layer,1);

layer.events.register("loadstart", this.getOLMap(), function(evt) { self.loadStart(evt); } );
layer.events.register("loadend", this.getOLMap(), function(evt) { self.loadEnd(evt); } );
