<?php 

class MapPageDecorator extends DataObjectDecorator {
	
	function extraStatics() {
		return array(
			'has_one' => array (
				'Map' => 'MapObject',
			)
		);
	}

	/**
	 * Update the CMS fields, adding some descriptions and text fields to 
	 * the Browse Page catalogue page.
	 */
	function updateCMSFields(FieldSet &$fields) {

		$items = array();
		$maps  = DataObject::get("MapObject");
		if ($maps) $items = $maps->map('ID','Title');

		$fields->addFieldsToTab("Root.Content.OpenLayers", 
			array(
				new LiteralField("MapLabel","<h2>Map Selection</h2>"),
				// Display parameters
				new CompositeField( 
					new CompositeField( 
						new LiteralField("DefLabel","<h3>Default OpenLayers Map</h3>"),
						new DropdownField("MapID", "Map", $items, $this->owner->MapID, null, true)
					)
				)
			)
		);
	}
}