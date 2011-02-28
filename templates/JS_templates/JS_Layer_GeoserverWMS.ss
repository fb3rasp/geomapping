var options = {layers: '$LayerName' , format: '$Format', transparent: $isTransparent };

var layer = new OpenLayers.Layer.WMS( '$ID', '$Storage.URL', options,  {alpha: true} );
layer.queryable = $isQueryable;
layer.setVisibility($isVisible);	

this.getOLMap().addLayer(layer);

layer.events.register("loadstart", this.getOLMap(), function(evt) { self.loadStart(evt); } );
layer.events.register("loadend", this.getOLMap(), function(evt) { self.loadEnd(evt); } );

