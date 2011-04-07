<?php
/**
 * @package mapping
 * @subpackage googlemap
 */

/**
 * 
 *
 * @package mapping
 * @subpackage googlemap
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class Layer_GoogleMap extends Layer {
	
	static $gmap_types = array(
		"Satellite" => "G_SATELLITE_MAP",
		"Map" => "G_NORMAL_MAP",
		"Terrain" => "G_PHYSICAL_MAP",
		"Hybrid" => "G_HYBRID_MAP"
	);
	
	static $db = array(
		'GMapTypeName' =>  "Enum(array('Satellite','Map','Terrain','Hybrid'),'Satellite')",
	);

	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields($parents);
		
		$fields->removeFieldFromTab("Root", "FeatureTypes");
		$fields->removeFieldFromTab("Root.Main", "Queryable");
		$fields->removeFieldFromTab("Root.Main", "Type");
		return $fields;
	}	


	protected function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$this->Type = 'contextual';
		return;
	}
		
	
	function getGMapType() {
		return self::$gmap_types[$this->GMapTypeName];
	}
	
	function getJavaScript() {
		return $this->renderWith('JS_Layer_GoogleMap');
	}
	
	function isSphericalMercator() {
		
		$retValue = false;
		
		if ($this->Map()) {
			if ($this->Map()->Projection == 'EPSG:900913') {
				$retValue = true;
			}
		}
		
		return $retValue;
	}
}