var layer = null;
var styles = null;

<% if StyleMap %>
<% control StyleMap %>
$JavaScript
<% end_control %>
<% else %>
// Use default stylemap for this WFS layer
<% end_if %>

var p = new OpenLayers.Protocol.WFS({
	url: "$Storage.URL_WFS",
 	featurePrefix: '$Namespace',
	featureType: '$FeatureType',
	<% if Version %>version: "$Version"<% end_if %>
});			

p.format.setNamespace("feature", "$Storage.URL_WFS");

strategies =  [
	new OpenLayers.Strategy.Fixed(),
//	new OpenLayers.Strategy.BBOX(),
];

layer = new OpenLayers.Layer.Vector("$Title", {
	styleMap: styles,
	strategies: strategies,
	protocol: p,
	<% if Projection %>projection: new OpenLayers.Projection("$Projection"),<% end_if %>
	queryable: $isQueryable 
});

this.getOLMap().addLayer(layer);

layer.setVisibility($isVisible);
