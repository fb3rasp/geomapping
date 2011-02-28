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
	
	function getGMapType() {
		return self::$gmap_types[$this->GMapTypeName];
	}
	
	function getJavaScript() {
		return $this->renderWith('JS_Layer_GoogleMap');
	}
}