var layer = null;

layer = new OpenLayers.Layer.GML('$ID', "$FileName")

this.getOLMap().addLayer(layer);

layer.setVisibility($isVisible);

layer.events.register("loadstart", this.getOLMap(), function(evt) { self.loadStart(evt); } );
layer.events.register("loadend", this.getOLMap(), function(evt) { self.loadEnd(evt); } );
