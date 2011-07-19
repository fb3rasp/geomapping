var layer = null;
var options = [];

// current assumption: if google maps layers are used, 
// we always use sphericalMercator.
layer = new OpenLayers.Layer.TMS(
    "$ID",
    "http://tile.openstreetmap.org/",
    {
        type: 'png', getURL: osm_getTileURL,
        displayOutsideMaxExtent: true,
        attribution: '<a href="http://www.openstreetmap.org/">OpenStreetMap</a>'
    }
);
		
layer.queryable = false;
layer.ssid = $ID;

this.getOLMap().addLayer(layer);

layer.setVisibility($isVisible);	
<% if Visible %>this.getOLMap().setBaseLayer(layer, true);<% end_if %>

this.getOLMap().setLayerZIndex(layer,1);

// wrapper function to return the url for the osm tilecache.
function osm_getTileURL(bounds) {
    var res = this.map.getResolution();
    var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
    var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
    var z = this.map.getZoom();
    var limit = Math.pow(2, z);

    if (y < 0 || y >= limit) {
        return OpenLayers.Util.getImagesLocation() + "404.png";
    } else {
        x = ((x % limit) + limit) % limit;
        return this.url + z + "/" + x + "/" + y + "." + this.type;
    }
}