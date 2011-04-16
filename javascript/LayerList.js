(function($) {
	$.entwine('ol', function($) {	

		$('.olLayerList h2.category').entwine({
			Highlight: '#BBCCEE',
			
			Background: '#FEFEFE',
		
			// collapse expand layers of category
			onclick: function() {
				this.next().slideToggle("fast");
			},
			
			// highlight (color)
			onmouseenter: function(){
				this.animate({backgroundColor: this.getHighlight()}, 
					{duration: 50, queue: false});
			},
			
			// reset highlight (color)
			onmouseleave: function(){
				this.animate({backgroundColor: this.getBackground()}, 
					{duration: 50, queue: false});
			}
		});
		
		$('.olLayerList ul.layers li').entwine({
			Highlight: '#BBCCEE',
			
			Background: '#FEFEFE',
		
			onmouseenter: function(){
				this.animate({backgroundColor: this.getHighlight()}, 
					{duration: 50, queue: false});
			},
			onmouseleave: function(){
				this.animate({backgroundColor: this.getBackground()}, 
					{duration: 50, queue: false});
			},
		
			onclick: function() {
				this.find(':checkbox').toggleLayerVisibility();
			}
					
		});
		
		$(".olLayerList form input:checkbox").entwine({

			getMap: function() {
				return $('.olMap:first');
			},
			
			onclick: function(event) {
				event.stopPropagation();
				this.setLayerVisibility(this.attr('checked'));
			},	
		
			/** 
			 * Update visibility of the selected layer.
			 */
			setLayerVisibility: function(visible) {
				var map = this.getMap().getOLMap();
		
				var name = this.parent().attr('layer');
				var layers = map.getLayersByName(name);
			
				if (layers.length > 0) {
					var layer = layers[0];
					layer.setVisibility(visible);
				}
			},
		
			toggleLayerVisibility: function() {
				var checked = !this.attr('checked');
				this.attr('checked',checked);
				this.setLayerVisibility(checked);
			},
		
			/** 
			 * Update visibility of the selected layer.
			 */
			showLayer: function() {
				setLayerVisibility(true);
			},
		
			/** 
			 * Update visibility of the selected layer.
			 */
			hideLayer: function() {
				setLayerVisibility(false);
			}			
		});
	});
}(jQuery));