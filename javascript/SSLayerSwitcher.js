/**
 * 
 */
OpenLayers.Control.SSLayerSwitcher = OpenLayers.Class(OpenLayers.Control.LayerSwitcher, {
	
	activeColor: 'transparent',
	
  redraw: function() {
      //if the state hasn't changed since last redraw, no need 
      // to do anything. Just return the existing div.
      if (!this.checkRedraw()) { 
          return this.div; 
      } 

      //clear out previous layers 
      this.clearLayersArray("base");
      this.clearLayersArray("data");
      
      var containsOverlays = false;
      var containsBaseLayers = false;
      
      // Save state -- for checking layer if the map state changed.
      // We save this before redrawing, because in the process of redrawing
      // we will trigger more visibility changes, and we want to not redraw
      // and enter an infinite loop.
      var len = this.map.layers.length;
      this.layerStates = new Array(len);
      for (var i=0; i <len; i++) {
          var layer = this.map.layers[i];
          this.layerStates[i] = {
              'name': layer.name, 
              'visibility': layer.visibility,
              'inRange': layer.inRange,
              'id': layer.id
          };
      }
    
      var ulElem = document.createElement('ul');

      var layers = this.map.layers.slice();
      if (!this.ascending) { layers.reverse(); }
      for(var i=0, len=layers.length; i<len; i++) {
          var layer = layers[i];
          var baseLayer = layer.isBaseLayer;

          if (layer.displayInLayerSwitcher) {

              if (baseLayer) {
                  containsBaseLayers = true;
              } else {
                  containsOverlays = true;
              }    

              // only check a baselayer if it is *the* baselayer, check data
              //  layers if they are visible
              var checked = (baseLayer) ? (layer == this.map.baseLayer)
                                        : layer.getVisibility();
  
							var liElem = document.createElement('li');
							if(checked) liElem.className = 'active';
							var liSpanElem = document.createElement('span');
							liSpanElem.className = 'inner';

              // create input element
              var inputElem = document.createElement("input");
              inputElem.id = this.id + "_input_" + layer.name;
              inputElem.name = (baseLayer) ? "baseLayers" : layer.name;
              inputElem.type = (baseLayer) ? "radio" : "checkbox";
              inputElem.value = layer.name;
              inputElem.checked = checked;
              inputElem.defaultChecked = checked;

              if (!baseLayer && !layer.inRange) {
                  inputElem.disabled = true;
              }
              var context = {
                  'inputElem': inputElem,
                  'layer': layer,
                  'layerSwitcher': this
              };
              OpenLayers.Event.observe(inputElem, "mouseup", 
                  OpenLayers.Function.bindAsEventListener(this.onInputClick,
                                                          context)
              );

							var labelElem = document.createElement('label');
							labelElem.setAttribute('for', inputElem.id);
              
              // create span
              var labelSpan = document.createElement("a");
              if (!baseLayer && !layer.inRange) {
                  labelSpan.style.color = "gray";
              }
              labelSpan.href = '#';
              labelSpan.innerHTML = layer.name;
              labelSpan.style.verticalAlign = (baseLayer) ? "bottom" 
                                                          : "baseline";
              OpenLayers.Event.observe(labelSpan, "click", 
                  OpenLayers.Function.bindAsEventListener(this.onInputClick,
                                                          context)
              );
              
              var groupArray = (baseLayer) ? this.baseLayers
                                           : this.dataLayers;
              groupArray.push({
                  'layer': layer,
                  'inputElem': inputElem,
                  'labelSpan': labelSpan
              });
                                                   
  
              var groupDiv = (baseLayer) ? this.baseLayersDiv
                                         : this.dataLayersDiv;
							groupDiv.appendChild(ulElem);
							ulElem.appendChild(liElem);
							liElem.appendChild(liSpanElem);
              liSpanElem.appendChild(inputElem);
              liSpanElem.appendChild(labelElem);
							labelElem.appendChild(labelSpan);
          }
      }

      // if no overlays, dont display the overlay label
      // this.dataLbl.style.display = (containsOverlays) ? "" : "none";        
      
      // if no baselayers, dont display the baselayer label
      // this.baseLbl.style.display = (containsBaseLayers) ? "" : "none";        

      return this.div;
  },

	/** 
   * Method: loadContents
   * Set up the labels and divs for the control
   */
  loadContents: function() {

      OpenLayers.Event.observe(this.div, "mouseup", 
          OpenLayers.Function.bindAsEventListener(this.mouseUp, this));
      OpenLayers.Event.observe(this.div, "click",
                    this.ignoreEvent);
      OpenLayers.Event.observe(this.div, "mousedown",
          OpenLayers.Function.bindAsEventListener(this.mouseDown, this));
      OpenLayers.Event.observe(this.div, "dblclick", this.ignoreEvent);


      // layers list div        
      this.layersDiv = document.createElement("div");
      this.layersDiv.id = this.id + "_layersDiv";
      this.layersDiv.style.paddingTop = "5px";
      this.layersDiv.style.paddingLeft = "10px";
      this.layersDiv.style.paddingBottom = "5px";
      this.layersDiv.style.paddingRight = "0px";
      this.layersDiv.style.backgroundColor = this.activeColor;        
      
      this.baseLayersDiv = document.createElement("div");
      this.baseLayersDiv.style.paddingLeft = "10px";
       
      this.dataLayersDiv = document.createElement("div");
      this.dataLayersDiv.style.paddingLeft = "10px";

      if (this.ascending) {
          // this.layersDiv.appendChild(this.baseLbl);
          this.layersDiv.appendChild(this.baseLayersDiv);
          // this.layersDiv.appendChild(this.dataLbl);
          this.layersDiv.appendChild(this.dataLayersDiv);
      } else {
          // this.layersDiv.appendChild(this.dataLbl);
          this.layersDiv.appendChild(this.dataLayersDiv);
          // this.layersDiv.appendChild(this.baseLbl);
          this.layersDiv.appendChild(this.baseLayersDiv);
      }    

      this.div.appendChild(this.layersDiv);
  }
});