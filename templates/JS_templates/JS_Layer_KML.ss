var layer = null;

layer = new OpenLayers.Layer.Vector('$ID', {
	strategies: [new OpenLayers.Strategy.Fixed()],
	protocol: new OpenLayers.Protocol.HTTP({
		
		url: "$FileName",
		format: new OpenLayers.Format.KML({
			extractStyles: true, 
			extractAttributes: true,
			maxDepth: 2
		})
	})
});

this.getOLMap().addLayer(layer);

layer.setVisibility($isVisible);

layer.events.register("loadstart", this.getOLMap(), function(evt) { self.loadStart(evt); } );
layer.events.register("loadend", this.getOLMap(), function(evt) { self.loadEnd(evt); } );
